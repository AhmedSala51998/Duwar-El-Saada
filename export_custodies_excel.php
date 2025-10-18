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

// جلب العهد
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

$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تحضير الاستعلام لجلب الحركات المرتبطة
$transactions_stmt = $pdo->prepare("SELECT * FROM custody_transactions WHERE custody_id=? ORDER BY created_at ASC");

// تجهيز بيانات Excel
$data = [];
$data[] = ["ID", "الشخص", "الوارد", "الصادر", "الرصيد", "تاريخ الاستلام", "ملاحظات", "تاريخ الإضافة"];

// متغيرات الرصيد والإجماليات
$balance = 0;
$total_in = 0;
$total_out = 0;

foreach ($rows as $r) {
    $in  = (float)$r['main_amount'];
    $out = $in - (float)$r['amount'];
    if($out < 0) $out = 0;

    $balance += $in - $out;
    $current_balance = $balance;

    $total_in += $in;
    $total_out += $out;

    // إضافة صف العهدة
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

    // الحركات المرتبطة بالعهدة
    $transactions_stmt->execute([$r['id']]);
    $transactions = $transactions_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($transactions as $t) {
        $trans_amount = (float)$t['amount'];
        $balance -= $trans_amount;
        /*$current_balance = $balance;*/

        // تحويل نوع الحركة للعربي
        $type_ar = '';
        switch($t['type']) {
            case 'asset': $type_ar = 'أصول'; break;
            case 'expense': $type_ar = 'مصروفات'; break;
            case 'purchase': $type_ar = 'مشتريات'; break;
            default: $type_ar = htmlspecialchars($t['type']); 
        }

        $total_out += $trans_amount;

        // إضافة صف الحركة
        $data[] = [
            '',
            "-- $type_ar",
            '',
            number_format($trans_amount, 2),
            number_format($current_balance, 2),
            $t['created_at'],
            $t['notes'] ?? '-',
            'حركة'
        ];
    }
}

// إضافة صف الإجماليات
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
$xlsx->downloadAs('custodies_with_transactions.xlsx');
?>