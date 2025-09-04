<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$q="SELECT * FROM subscriptions WHERE 1"; $ps=[];
if($kw!==''){ $q.=" AND service_name LIKE ?"; $ps[]="%$kw%"; }
$q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); $s->execute($ps); $rows=$s->fetchAll(PDO::FETCH_ASSOC);

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
