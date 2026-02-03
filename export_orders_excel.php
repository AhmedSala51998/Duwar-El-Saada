<?php
require __DIR__.'/config/config.php';
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';
require_permission('orders.print_excel');

$kw        = trim($_GET['kw'] ?? '');
$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$branch_id = $_GET['branch_id'] ?? '';

$params = [];

/* === منطق اليوم / أمس === */
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

/* =====================
   الاستعلام (مع الفرع)
===================== */
$q = "
SELECT 
    o.id,
    p.name AS pname,
    o.qty,
    o.unit,
    o.note,
    o.created_at,
    b.branch_name
FROM orders o
JOIN purchases p ON p.id = o.purchase_id
LEFT JOIN orders_purchases op ON op.id = p.order_id
LEFT JOIN branches b ON b.id = op.branch_id
WHERE 1
";

/* === البحث (منتج + فرع) === */
if ($kw !== '') {
    $q .= " AND (
        p.name LIKE ?
        OR b.branch_name LIKE ?
    )";
    $params[] = "%$kw%";
    $params[] = "%$kw%";
}

/* === فلترة الفرع === */
if (!empty($branch_id) && $branch_id != 0) {
    $q .= " AND op.branch_id = ?";
    $params[] = $branch_id;
}

/* === فلترة التواريخ === */
if ($from_date !== '') {
    $q .= " AND DATE(o.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date !== '') {
    $q .= " AND DATE(o.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY o.id DESC";

/* === تنفيذ === */
$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   تجهيز بيانات الإكسيل
===================== */
$data = [];
$data[] = ["ID", "الفرع", "المنتج", "الكمية", "الوحدة", "ملاحظة", "التاريخ"];

foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['branch_name'] ?? '-',
        $r['pname'],
        $r['qty'],
        $r['unit'],
        $r['note'],
        $r['created_at']
    ];
}

/* === إنشاء وتنزيل الملف === */
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('orders.xlsx');
