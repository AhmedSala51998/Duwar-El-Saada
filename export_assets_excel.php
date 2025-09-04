<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT id, name, type, quantity, price, payer_name, created_at FROM assets WHERE 1";
$params = [];

if ($kw !== '') {
    $q .= " AND name LIKE ?";
    $params[] = "%$kw%";
}
$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

$data = [];
$data[] = ["ID", "الاسم", "النوع", "العدد", "السعر", "الدافع", "التاريخ"];

foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['name'],
        $r['type'],
        $r['quantity'],
        $r['price'],
        $r['payer_name'],
        $r['created_at']
    ];
}

$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('assetes.xlsx');
