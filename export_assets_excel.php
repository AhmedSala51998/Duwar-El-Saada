<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$kw        = trim($_GET['kw'] ?? '');

// ضبط التواريخ بناءً على نوع التاريخ
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// استعلام الأصول
$q = "SELECT id, name, type, quantity, price, has_vat, payer_name, payment_source, created_at FROM assets WHERE 1";
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
$data[] = ["ID", "الاسم", "النوع", "العدد", "السعر", "الإجمالي الطبيعي", "الضريبة (15%)", "الإجمالي بعد الضريبة", "الدافع", "مصدر الدفع", "التاريخ"];

foreach ($rows as $r) {
    $quantity = (float)$r['quantity'];
    $price = (float)$r['price'];
    $total = $quantity * $price;

    if (!empty($r['has_vat']) && $r['has_vat'] == 1) {
        $vat = $total * 0.15;
        $total_with_vat = $total + $vat;
    } else {
        $vat = 0;
        $total_with_vat = $total;
    }

    $data[] = [
        $r['id'],
        $r['name'],
        $r['type'],
        $quantity,
        $price,
        $total,
        $vat,
        $total_with_vat,
        $r['payer_name'],
        $r['payment_source'] ?? '-',
        $r['created_at']
    ];
}

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('assets.xlsx');
?>