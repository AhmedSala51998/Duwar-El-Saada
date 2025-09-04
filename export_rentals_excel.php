<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM rentals WHERE 1"; 
$ps = [];
if($kw!==''){ $q .= " AND rental_name LIKE ?"; $ps[] = "%$kw%"; }
$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q); 
$s->execute($ps); 
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

$data = [["ID","اسم الإيجار","نوع الدفع","السعر","نوع الإيجار","الدافع","التاريخ"]];
foreach($rows as $r){
    $data[] = [
        $r['id'],
        $r['rental_name'],
        $r['payment_type'],
        $r['rental_price'],
        $r['rental_kind'],
        $r['payer'],
        $r['created_at']
    ];
}

Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('rentals.xlsx');
