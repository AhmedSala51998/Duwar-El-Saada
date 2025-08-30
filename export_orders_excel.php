<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php'; // نزل المكتبة وحطها هنا

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT o.id, p.name pname, o.qty, o.unit, o.note, o.created_at
      FROM orders o 
      JOIN purchases p ON p.id = o.purchase_id
      WHERE 1";
$params = [];

if ($kw !== '') {
    $q .= " AND p.name LIKE ?";
    $params[] = "%$kw%";
}
$q .= " ORDER BY o.id DESC";

$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات للـ Excel
$data = [];
$data[] = ["ID", "المنتج", "الكمية", "الوحدة", "ملاحظة", "التاريخ"]; // العناوين

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
