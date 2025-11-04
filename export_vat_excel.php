<?php
require __DIR__.'/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';
require_permission('reports.report_vat_excel');

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$params = [];
$purchasesFilter = '';
$expensesFilter  = '';
$assetsFilter    = '';

if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

if($from_date) {
    $purchasesFilter .= " AND DATE(op.created_at) >= ?";
    $expensesFilter  .= " AND DATE(e.created_at) >= ?";
    $assetsFilter    .= " AND DATE(a.created_at) >= ?";
    $params[] = $from_date;
}
if($to_date) {
    $purchasesFilter .= " AND DATE(op.created_at) <= ?";
    $expensesFilter  .= " AND DATE(e.created_at) <= ?";
    $assetsFilter    .= " AND DATE(a.created_at) <= ?";
    $params[] = $to_date;
}

// ---------------------------- المشتريات ----------------------------
/*$stmt = $pdo->prepare("
    SELECT 
        p.name,
        o.supplier_name,
        o.created_at,
        ROUND(CASE WHEN p.unit_vat=0 THEN (p.total_price * p.total_packages * 1.15) ELSE (p.total_price * p.total_packages) END, 2) AS `before`,
        ROUND(CASE WHEN p.unit_vat=0 THEN 0 ELSE (p.total_price * p.total_packages * 0.15) END, 2) AS `vat`,
        ROUND(CASE WHEN p.unit_vat=0 THEN (p.total_price * p.total_packages * 1.15) ELSE (p.total_price * p.total_packages * 1.15) END, 2) AS `after`
    FROM purchases p
    LEFT JOIN orders_purchases o ON p.order_id = o.id
    WHERE 1=1 $purchasesFilter
");*/
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        op.supplier_name,
        op.created_at,

        CASE 
            WHEN op.vat > 0 THEN p.unit_total
            ELSE p.unit_all_total
        END AS `before`,

        CASE 
            WHEN op.vat > 0 THEN p.unit_vat
            ELSE 0
        END AS `vat`,

        CASE 
            WHEN op.vat > 0 THEN p.unit_all_total
            ELSE p.unit_all_total
        END AS `after`,

        CASE 
            WHEN op.vat > 0 THEN p.price
            ELSE p.price + (p.price * 0.15)
        END AS `price`

    FROM purchases p
    LEFT JOIN orders_purchases op ON p.order_id = op.id
    WHERE 1=1 $purchasesFilter
");
$stmt->execute($params);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- المصروفات ----------------------------
/*$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN sub_expense = 'أخرى' OR sub_expense IS NULL OR sub_expense = '' 
            THEN CONCAT(main_expense, ' - ', expense_desc)
            ELSE CONCAT(main_expense, ' - ', sub_expense)
        END AS name,
        ROUND(CASE WHEN has_vat=1 THEN expense_amount ELSE expense_amount * 1.15 END , 2) AS `before`,
        ROUND(CASE WHEN has_vat=1 THEN expense_amount * 0.15 ELSE 0 END, 2) AS `vat`,
        ROUND(CASE WHEN has_vat=1 THEN expense_amount * 1.15 ELSE expense_amount * 1.15 END, 2) AS `after`,
        created_at
    FROM expenses
    WHERE 1=1 $expensesFilter
");*/
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN e.sub_expense = 'أخرى' OR e.sub_expense IS NULL OR e.sub_expense = '' 
            THEN CONCAT(e.main_expense, ' - ', e.expense_desc)
            ELSE CONCAT(e.main_expense, ' - ', e.sub_expense)
        END AS name,

        CASE 
            WHEN e.has_vat = 1 THEN e.expense_amount 
            ELSE e.total_amount 
        END AS `before`,

        CASE 
            WHEN e.has_vat = 1 THEN e.vat_value 
            ELSE 0 
        END AS `vat`,

        CASE 
            WHEN e.has_vat = 1 THEN e.total_amount 
            ELSE e.total_amount 
        END AS `after`,

        e.created_at

    FROM expenses e
    WHERE 1=1 $expensesFilter
");
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- الأصول ----------------------------
/*$stmt = $pdo->prepare("
    SELECT 
        name,
        quantity,
        type,
        created_at,
        ROUND(CASE WHEN has_vat=1 THEN (price * quantity) ELSE price * quantity * 1.15 END, 2) AS `before`,
        ROUND(CASE WHEN has_vat=1 THEN price * quantity * 0.15 ELSE 0 END, 2) AS `vat`,
        ROUND(CASE WHEN has_vat=1 THEN price * quantity * 1.15 ELSE price * quantity * 1.15 END, 2) AS `after`
    FROM assets
    WHERE 1=1 $assetsFilter
");*/
$stmt = $pdo->prepare("
    SELECT 
        a.name,
        a.quantity,
        a.type,
        a.created_at,

        CASE 
            WHEN a.has_vat = 1 THEN (a.price * a.quantity)
            ELSE a.total_amount
        END AS `before`,

        CASE 
            WHEN a.has_vat = 1 THEN a.vat_value
            ELSE 0
        END AS `vat`,

        CASE 
            WHEN a.has_vat = 1 THEN a.total_amount
            ELSE a.total_amount
        END AS `after`

    FROM assets a
    WHERE 1=1 $assetsFilter
");
$stmt->execute($params);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- تجميع البيانات في ملف Excel ----------------------------
$data = [];
$data[] = ["المصدر", "الاسم", "اسم المورد / العدد والنوع", "الإجمالي قبل الضريبة", "الضريبة", "الإجمالي بعد","التاريخ"];

$totalBefore = $totalVat = $totalAfter = 0;

// المشتريات
foreach ($purchases as $r) {
    $beforeValue = ($r['vat'] == 0) ? $r['after'] : $r['before'];
    $data[] = ["المشتريات", $r['name'], $r['supplier_name'], $beforeValue, $r['vat'], $r['after'], $r['created_at']];
    $totalBefore += $beforeValue;
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// المصروفات
foreach ($expenses as $r) {
    $beforeValue = ($r['vat'] == 0) ? $r['after'] : $r['before'];
    $data[] = ["المصروفات", $r['name'], "", $beforeValue, $r['vat'], $r['after'], $r['created_at']];

    $totalBefore += $beforeValue;
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// الأصول
foreach ($assets as $r) {
    $typeInfo = trim(($r['quantity'] ?? '') . '-' . ($r['type'] ?? ''));
    $beforeValue = ($r['vat'] == 0) ? $r['after'] : $r['before'];
    $data[] = ["الأصول", $r['name'], $typeInfo, $beforeValue, $r['vat'], $r['after'], $r['created_at']];

    $totalBefore += $beforeValue;
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// الإجماليات النهائية
$data[] = [];
if($totalVat != 0){
 $data[] = ["الإجماليات الكلية", "", "", round($totalBefore, 2), round($totalVat, 2), round($totalAfter, 2)];
}else{
 $data[] = ["الإجماليات الكلية", "", "", round($totalBefore, 2), 0 , round($totalAfter, 2)];
}

// ---------------------------- تحديد عنوان التقرير ----------------------------
if ($date_type === 'today') {
    $title = "🟢 تقرير اليوم (" . date('Y-m-d') . ")";
} elseif ($date_type === 'yesterday') {
    $title = "⚫ تقرير أمس (" . date('Y-m-d', strtotime('-1 day')) . ")";
} elseif ($from_date || $to_date) {
    $fromText = $from_date ?: 'بداية';
    $toText = $to_date ?: 'اليوم';
    $title = "🟠 تقرير مخصص من $fromText إلى $toText";
} else {
    $title = "كل التقارير";
}

// ---------------------------- إنشاء وتحميل الملف ----------------------------
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data, $title);
$xlsx->downloadAs('vat_report.xlsx');
?>