<?php
require __DIR__.'/config/config.php';
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw        = trim($_GET['kw'] ?? '');
$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$params = [];

// تطبيق منطق الفلترة حسب نوع التاريخ
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// بناء الاستعلام
$q = "SELECT id, person_name, main_amount, taken_at, notes, created_at FROM custodies WHERE 1";

// فلترة بالكلمة المفتاحية
if ($kw !== '') {
    $q .= " AND person_name LIKE ?";
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
$data[] = ["ID", "الشخص", "المبلغ", "تاريخ الاستلام", "ملاحظات", "تاريخ الإضافة"];

foreach ($rows as $r) {
    $data[] = [
        $r['id'],
        $r['person_name'],
        $r['main_amount'],
        $r['taken_at'],
        $r['notes'] ?? '-',
        $r['created_at']
    ];
}

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('custodies.xlsx');
?>
