<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php'; // لازم تنزل المكتبة وتحطها في libs/

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT id, name, price, payer_name, created_at FROM assets WHERE 1";
$params = [];

if ($kw !== '') {
    $q .= " AND name LIKE ?";
    $params[] = "%$kw%";
}
$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات
$data = [];
$data[] = ["ID", "الاسم", "السعر", "الدافع", "التاريخ"]; // العناوين

foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['name'],
        $r['price'],
        $r['payer_name'],
        $r['created_at']
    ];
}

// إنشاء الملف وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('assets.xlsx');
