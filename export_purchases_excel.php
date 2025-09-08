<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q = "SELECT id,name,quantity,unit,price,payer_name,created_at FROM purchases WHERE 1";
$params = [];

// فلترة بالكلمة المفتاحية
if($kw !== '') { 
    $q .= " AND name LIKE ?"; 
    $params[] = "%$kw%"; 
}

// فلترة بالتواريخ
if($from_date !== '') {
    $q .= " AND DATE(created_at) >= ?";
    $params[] = $from_date;
}
if($to_date !== '') {
    $q .= " AND DATE(created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q); 
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات كـ array
$data = [];
$data[] = ["ID","الاسم","الكمية","الوحدة","السعر","الدافع","التاريخ"];
foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['name'],
        $r['quantity'],
        $r['unit'],
        $r['price'],
        $r['payer_name'],
        $r['created_at']
    ];
}

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('purchases.xlsx');
