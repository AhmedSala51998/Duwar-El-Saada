<?php
require __DIR__.'/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$dateFilter = '';
$params = [];
if($from_date) { $dateFilter .= " AND DATE(created_at) >= ?"; $params[] = $from_date; }
if($to_date)   { $dateFilter .= " AND DATE(created_at) <= ?"; $params[] = $to_date; }

// ---------------------------- المشتريات ----------------------------
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        o.supplier_name,
        ROUND(p.price * p.quantity, 2) AS `before`,
        ROUND(p.price * p.quantity * 0.15, 2) AS `vat`,
        ROUND(p.price * p.quantity * 1.15, 2) AS `after`
    FROM purchases p
    LEFT JOIN orders_purchases o ON p.order_id = o.id
    WHERE 1=1 $dateFilter
");
$stmt->execute($params);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- المصروفات ----------------------------
$stmt = $pdo->prepare("SELECT 
    CASE 
        WHEN sub_expense = 'أخرى' OR sub_expense IS NULL OR sub_expense = '' 
        THEN CONCAT(main_expense, ' - ', expense_desc)
        ELSE CONCAT(main_expense, ' - ', sub_expense)
    END AS name,
    ROUND(expense_amount, 2) AS `before`,
    ROUND(CASE WHEN has_vat=1 THEN expense_amount * 0.15 ELSE 0 END, 2) AS `vat`,
    ROUND(CASE WHEN has_vat=1 THEN expense_amount * 1.15 ELSE expense_amount END, 2) AS `after`
FROM expenses
WHERE 1=1 $dateFilter");
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- الأصول ----------------------------
$stmt = $pdo->prepare("
    SELECT 
        name,
        quantity,
        type,
        ROUND(price * quantity, 2) AS `before`,
        ROUND(CASE WHEN has_vat=1 THEN price * quantity * 0.15 ELSE 0 END, 2) AS `vat`,
        ROUND(CASE WHEN has_vat=1 THEN price * quantity * 1.15 ELSE price * quantity END, 2) AS `after`
    FROM assets
    WHERE 1=1 $dateFilter
");
$stmt->execute($params);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- تجميع البيانات في ملف Excel ----------------------------
$data = [];
$data[] = ["المصدر", "الاسم", "اسم المورد/العدد والنوع", "الإجمالي قبل الضريبة", "الضريبة", "الإجمالي بعد"];

$totalBefore = $totalVat = $totalAfter = 0;

// المشتريات
// المشتريات
foreach ($purchases as $r) {
    $data[] = ["المشتريات", $r['name'], $r['supplier_name'], $r['before'], $r['vat'], $r['after']];
    $totalBefore += $r['before'];
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// المصروفات
foreach ($expenses as $r) {
    $data[] = ["المصروفات", $r['name'], "", $r['before'], $r['vat'], $r['after']];
    $totalBefore += $r['before'];
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// الأصول
foreach ($assets as $r) {
    $typeInfo = $r['quantity'] . ' ' . $r['unit'];
    $data[] = ["الأصول", $r['name'], $typeInfo, $r['before'], $r['vat'], $r['after']];
    $totalBefore += $r['before'];
    $totalVat += $r['vat'];
    $totalAfter += $r['after'];
}

// الإجماليات النهائية
$data[] = [];
$data[] = ["الإجماليات الكلية", "", "", round($totalBefore,2), round($totalVat,2), round($totalAfter,2)];

// إنشاء الملف وتحميله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('vat_report.xlsx');
?>
