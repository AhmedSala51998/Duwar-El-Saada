<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q = "SELECT p.*, o.invoice_serial
      FROM purchases p
      LEFT JOIN orders_purchases o ON p.order_id = o.id
      WHERE 1";

$params = [];
if($kw !== '') { 
    $q .= " AND p.name LIKE ?"; 
    $params[] = "%$kw%"; 
}

if($from_date !== '') {
    $q .= " AND DATE(p.created_at) >= ?";
    $params[] = $from_date;
}
if($to_date !== '') {
    $q .= " AND DATE(p.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY p.id DESC";

$s = $pdo->prepare($q); 
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);


// تجهيز البيانات كـ array
$data = [];
$data[] = ["ID","رقم تسلسلي","الاسم","الكمية","الوحدة","السعر","الدافع","مصدر الدفع","التاريخ"];
foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['invoice_serial'] ?? '-',
        $r['name'],
        $r['quantity'],
        $r['unit'],
        $r['price'],
        $r['payer_name'],
        $r['payment_source'],
        $r['created_at']
    ];
}


// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('purchases.xlsx');
