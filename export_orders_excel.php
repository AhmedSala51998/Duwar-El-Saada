<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q = "SELECT o.id, p.name pname, o.qty, o.unit, o.note, o.created_at
      FROM orders o 
      JOIN purchases p ON p.id = o.purchase_id
      WHERE 1";
$params = [];

// فلترة بالكلمة المفتاحية
if ($kw !== '') {
    $q .= " AND p.name LIKE ?";
    $params[] = "%$kw%";
}

// فلترة بالتواريخ
if ($from_date !== '') {
    $q .= " AND DATE(o.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date !== '') {
    $q .= " AND DATE(o.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY o.id DESC";

$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات للـ Excel
$data = [];
$data[] = ["ID", "المنتج", "الكمية", "الوحدة", "ملاحظة", "التاريخ"];

foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['pname'],
        $r['qty'],
        $r['unit'],
        $r['note'],
        $r['created_at']
    ];
}

// إنشاء وتنزيل الملف
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('orders.xlsx');
