<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q = "SELECT * FROM expenses WHERE 1"; 
$ps = [];

// فلترة بالكلمة المفتاحية
if($kw !== ''){ 
    $q .= " AND main_expense LIKE ?"; 
    $ps[] = "%$kw%"; 
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

$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q); 
$s->execute($ps); 
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

$data = [["ID","الخانة الأولى","الخانة الثانية","بيان المصروف","قيمة المصروف","المرفق","التاريخ"]];
foreach($rows as $r){
    $data[] = [
        $r['id'],
        $r['main_expense'],
        $r['sub_expense'],
        $r['expense_desc'],
        $r['expense_amount'],
        $r['expense_file'],
        $r['created_at'] ?? ''
    ];
}

Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('expenses.xlsx');
