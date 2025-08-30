<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

// استعلام البيانات
$kw = trim($_GET['kw'] ?? '');
$q = "SELECT id,name,quantity,unit,price,payer_name,created_at FROM purchases WHERE 1";
$params=[];
if($kw!==''){ 
    $q.=" AND name LIKE ?"; 
    $params[]="%$kw%"; 
}
$q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); 
$s->execute($params);
$rows=$s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات كـ array
$data = [];
$data[] = ["ID","الاسم","الكمية","الوحدة","السعر","الدافع","التاريخ"]; // العناوين
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
