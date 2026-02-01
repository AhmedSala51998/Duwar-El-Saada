<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';
require_permission('assets.print_excel');

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$kw        = trim($_GET['kw'] ?? '');
$branch_id = $_GET['branch_id'] ?? '';

// ضبط التواريخ بناءً على نوع التاريخ
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// استعلام الأصول مع اسم الفرع
$q = "SELECT a.*, b.branch_name 
      FROM assets a
      LEFT JOIN branches b ON a.branch_id = b.id
      WHERE 1";
$params = [];

// فلترة بالكلمة المفتاحية
if ($kw !== '') {
    $q .= " AND a.name LIKE ?";
    $params[] = "%$kw%";
}

// فلترة بالتواريخ
if ($from_date !== '') {
    $q .= " AND DATE(a.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date !== '') {
    $q .= " AND DATE(a.created_at) <= ?";
    $params[] = $to_date;
}

// فلترة بالفرع إذا تم تحديده
if ($branch_id !== '') {
    $q .= " AND a.branch_id = ?";
    $params[] = $branch_id;
}

$q .= " ORDER BY a.id DESC";

// تنفيذ الاستعلام
$s = $pdo->prepare($q);
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات للتصدير
$data = [];
$data[] = ["ID", "الاسم", "النوع", "العدد", "السعر", "الإجمالي الطبيعي", "الضريبة (15%)", "الإجمالي بعد الضريبة", "الدافع", "مصدر الدفع", "الفرع", "التاريخ"];

foreach ($rows as $r) {
    $quantity = (float)$r['quantity'];
    $price = (float)$r['price'];
    $total = $quantity * $price;
    $price1 = 0;

    if (!empty($r['has_vat']) && $r['has_vat'] == 1) {
        $vat = (float)$r['vat_value'];
        $total_with_vat = $total + $vat;
        $price1 = $r['price'];
    } else {
        $vat = 0;
        $total_with_vat = $r['total_amount'];
        $total = $r['total_amount'];
        $price1 = $r['price'] + ($r['price'] * 0.15);
    }

    $data[] = [
        $r['id'],
        $r['name'],
        $r['type'],
        $quantity,
        $price1,
        $total,
        $vat,
        $total_with_vat,
        $r['payer_name'],
        $r['payment_source'] ?? '-',
        $r['branch_name'] ?? '-',
        $r['created_at']
    ];
}

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('assets.xlsx');
?>