<?php
require __DIR__.'/config/config.php';
require_auth();

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// بناء الاستعلام
$q = "SELECT id, name, type, quantity, price, payer_name, payment_source, created_at FROM assets WHERE 1";
$params = [];

// فلترة بالكلمة المفتاحية
if ($kw !== '') {
    $q .= " AND name LIKE ?";
    $params[] = "%$kw%";
}

// فلترة بالتواريخ
if ($from_date !== '') {
    $q .= " AND DATE(created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date !== '') {
    $q .= " AND DATE(created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY id DESC";

// تنفيذ الاستعلام
$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات للتصدير
$data = [];
$data[] = ["ID", "الاسم", "النوع", "العدد", "السعر", "الدافع", "مصدر الدفع", "التاريخ"];

foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['name'],
        $r['type'],
        $r['quantity'],
        $r['price'],
        $r['payer_name'],
        $r['payment_source'] ?? '-',
        $r['created_at']
    ];
}

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('assets.xlsx');
