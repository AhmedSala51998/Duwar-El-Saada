<?php
require __DIR__.'/config/config.php';
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';
require_permission('custodies.print_excel');

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

// استعلام العهد
$q = "SELECT id, person_name, main_amount,sub_amount, amount, taken_at, notes, created_at FROM custodies WHERE 1";

if ($kw !== '') {
    $q .= " AND person_name LIKE ?";
    $params[] = "%$kw%";
}
if ($from_date) {
    $q .= " AND DATE(created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date) {
    $q .= " AND DATE(created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY taken_at ASC";

$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// استعلام الحركات
$transactions_stmt = $pdo->prepare("SELECT * FROM custody_transactions WHERE custody_id=? ORDER BY created_at ASC");

$data = [];
$data[] = ["ID", "البيان", "الوارد", "الصادر", "الرصيد", "تاريخ الاستلام", "ملاحظات", "تاريخ الإضافة"];

$last_balance = 0;
$total_in = 0;
$total_out = 0;

foreach ($rows as $r) {
    $in = (float)$r['main_amount'];
    $remain = (float)$r['sub_amount'];
    $out = $in - $remain;
    if ($out < 0) $out = 0;

    // جلب الحركات
    $transactions_stmt->execute([$r['id']]);
    $transactions = $transactions_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($transactions) > 0) {
        $current_balance = $in - $out;
    } else {
        $current_balance = $last_balance + $in - $out;
    }

    $last_balance = $current_balance;

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

    $total_in += $in;
    $total_out += $out;

    // إضافة الحركات
    foreach ($transactions as $t) {
        $trans_amount = (float)$t['amount'];
        $current_balance -= $trans_amount;
        $last_balance = $current_balance;

        $type_ar = '';
        switch ($t['type']) {
            case 'asset': $type_ar = 'أصول'; break;
            case 'expense': $type_ar = 'مصروفات'; break;
            case 'purchase': $type_ar = 'مشتريات'; break;
            default: $type_ar = $t['type'];
        }

        $data[] = [
            '',
            "-- {$r['person_name']} -- $type_ar",
            '',
            number_format($trans_amount, 2),
            number_format($current_balance, 2),
            $t['created_at'],
            $t['notes'] ?? '',
            $type_ar
        ];

        $total_out += $trans_amount;
    }
}

// صف الإجماليات
$data[] = [
    '',
    'الإجماليات',
    number_format($total_in, 2),
    number_format($total_out, 2),
    number_format($last_balance, 2),
    '',
    '',
    ''
];

// إنشاء Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('custodies.xlsx');
?>
