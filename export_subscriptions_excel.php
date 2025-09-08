<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q="SELECT * FROM subscriptions WHERE 1"; 
$ps=[];

// فلترة بالكلمة المفتاحية
if($kw !== ''){ 
    $q .= " AND service_name LIKE ?"; 
    $ps[]="%$kw%"; 
}

// فلترة بالتواريخ
if($from_date !== '') {
    $q .= " AND DATE(created_at) >= ?";
    $ps[] = $from_date;
}
if($to_date !== '') {
    $q .= " AND DATE(created_at) <= ?";
    $ps[] = $to_date;
}

$q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); 
$s->execute($ps); 
$rows=$s->fetchAll(PDO::FETCH_ASSOC);

$data=[["ID","اسم الخدمة","المشتركون","نوع الاشتراك","السعر","الدافع","التاريخ"]];
foreach($rows as $r){
    $data[]=[
        $r['id'],
        $r['service_name'],
        $r['subscribers'],
        $r['subscription_type'],
        $r['service_price'],
        $r['payer'],
        $r['created_at']
    ];
}

Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('subscriptions.xlsx');
