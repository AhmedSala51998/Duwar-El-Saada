<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM gov_fees WHERE 1"; 
$ps = [];
if($kw!==''){ $q .= " AND fee_title LIKE ?"; $ps[] = "%$kw%"; }
$q .= " ORDER BY id DESC";
$s = $pdo->prepare($q); $s->execute($ps); $rows = $s->fetchAll(PDO::FETCH_ASSOC);

$data = [["ID","عنوان الرسوم","نوع الرسوم","السعر","الدافع","التاريخ"]];
foreach($rows as $r){
    $data[] = [
        $r['id'],
        $r['fee_title'],
        $r['fee_type'],
        $r['fee_amount'],
        $r['payer'],
        $r['created_at']
    ];
}

Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('gov_fees.xlsx');
