<?php
require __DIR__.'/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

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
    $purchasesFilter .= " AND DATE(o.created_at) >= ?";
    $expensesFilter  .= " AND DATE(expenses.created_at) >= ?";
    $assetsFilter    .= " AND DATE(assets.created_at) >= ?";
    $params[] = $from_date;
}
if($to_date) {
    $purchasesFilter .= " AND DATE(o.created_at) <= ?";
    $expensesFilter  .= " AND DATE(expenses.created_at) <= ?";
    $assetsFilter    .= " AND DATE(assets.created_at) <= ?";
    $params[] = $to_date;
}

// ---------------------------- المشتريات ----------------------------
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        o.supplier_name,
        ROUND(CASE WHEN p.unit_vat=0 THEN (p.price * p.quantity * 1.15) ELSE (p.price * p.quantity) END, 2) AS `before`,
        ROUND(CASE WHEN p.unit_vat=0 THEN 0 ELSE (p.price * p.quantity * 0.15) END, 2) AS `vat`,
        ROUND(CASE WHEN p.unit_vat=0 THEN (p.price * p.quantity * 1.15) ELSE (p.price * p.quantity * 1.15) END, 2) AS `after`
    FROM purchases p
    LEFT JOIN orders_purchases o ON p.order_id = o.id
    WHERE 1=1 $purchasesFilter
");
$stmt->execute($params);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- المصروفات ----------------------------
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN sub_expense = 'أخرى' OR sub_expense IS NULL OR sub_expense = '' 
            THEN CONCAT(main_expense, ' - ', expense_desc)
            ELSE CONCAT(main_expense, ' - ', sub_expense)
        END AS name,
        ROUND(CASE WHEN has_vat=1 THEN expense_amount ELSE expense_amount * 1.15 END , 2) AS `before`,
        ROUND(CASE WHEN has_vat=1 THEN expense_amount * 0.15 ELSE 0 END, 2) AS `vat`,
        ROUND(CASE WHEN has_vat=1 THEN expense_amount * 1.15 ELSE expense_amount * 1.15 END, 2) AS `after`
    FROM expenses
    WHERE 1=1 $expensesFilter
");
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- الأصول ----------------------------
$stmt = $pdo->prepare("
    SELECT 
        name,
        quantity,
        type,
        ROUND(CASE WHEN has_vat=1 THEN (price * quantity) ELSE price * quantity * 1.15 END, 2) AS `before`,
        ROUND(CASE WHEN has_vat=1 THEN price * quantity * 0.15 ELSE 0 END, 2) AS `vat`,
        ROUND(CASE WHEN has_vat=1 THEN price * quantity * 1.15 ELSE price * quantity * 1.15 END, 2) AS `after`
    FROM assets
    WHERE 1=1 $assetsFilter
");
$stmt->execute($params);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- تجميع البيانات في ملف Excel ----------------------------
$data = [];
$data[] = ["المصدر", "الاسم", "اسم المورد / العدد والنوع", "الإجمالي قبل الضريبة", "الضريبة", "الإجمالي بعد"];

$totalBefore = $totalVat = $totalAfter = 0;

// المشتريات
foreach ($purchases as $r) {
    $beforeValue = ($r['vat'] == 0) ? $r['after'] : $r['before'];
    $data[] = ["المشتريات", $r['name'], $r['supplier_name'], $beforeValue, $r['vat'], $r['after']];
    $totalBefore += $beforeValue;
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// المصروفات
foreach ($expenses as $r) {
    $beforeValue = ($r['vat'] == 0) ? $r['after'] : $r['before'];
    $data[] = ["المصروفات", $r['name'], "", $beforeValue, $r['vat'], $r['after']];

    $totalBefore += $beforeValue;
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// الأصول
foreach ($assets as $r) {
    $typeInfo = trim(($r['quantity'] ?? '') . '-' . ($r['type'] ?? ''));
    $beforeValue = ($r['vat'] == 0) ? $r['after'] : $r['before'];
    $data[] = ["الأصول", $r['name'], $typeInfo, $beforeValue, $r['vat'], $r['after']];

    $totalBefore += $beforeValue;
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// الإجماليات النهائية
$data[] = [];
if($totalVat != 0){
 $data[] = ["الإجماليات الكلية", "", "", round($totalBefore, 2), round($totalVat, 2), round($totalAfter, 2)];
}else{
 $data[] = ["الإجماليات الكلية", "", "", round($totalBefore, 2),0 , round($totalAfter, 2)];
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