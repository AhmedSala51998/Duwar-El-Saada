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
$q = "SELECT id, person_name, main_amount, amount, taken_at, notes, created_at FROM custodies WHERE 1";

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

$q .= " ORDER BY id ASC";

// تنفيذ الاستعلام
$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات للتصدير
// تجهيز البيانات للتصدير
$data = [];
$data[] = ["ID", "الشخص", "الوارد", "الصادر", "الرصيد", "تاريخ الاستلام", "ملاحظات", "تاريخ الإضافة"];

$balance = 0; // الرصيد السابق

foreach ($rows as $r) {
    $in  = (float)$r['main_amount']; // الوارد
    $out = (float)$r['amount'];      // الصادر

    if ($in > 0 || $out > 0) {
        $balance = $balance + $in - $out; // لو في حركة نحسب الرصيد
        $current_balance = $balance;
    } else {
        $current_balance = 0; // مفيش حركة
    }

    $data[] = [
        $r['id'],
        $r['person_name'],
        number_format($in, 2),
        number_format($out, 2),
        number_format($current_balance, 2),
        $r['taken_at'],
        $r['notes'] ?? '-',
        $r['created_at']
    ];
}

// صف الإجماليات في النهاية
$total_in  = array_sum(array_column($rows, 'main_amount'));
$total_out = array_sum(array_column($rows, 'amount'));

$data[] = [
    '',
    'الإجمالي الكلي',
    number_format($total_in, 2),
    number_format($total_out, 2),
    number_format($balance, 2),
    '',
    '',
    ''
];

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('custodies.xlsx');

?>
