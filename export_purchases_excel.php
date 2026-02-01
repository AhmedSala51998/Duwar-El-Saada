<?php
require __DIR__.'/config/config.php';
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';
require_permission('purchases.print_excel');

$kw        = trim($_GET['kw'] ?? '');
$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$branch_id = $_GET['branch_id'] ?? '';

/* منطق اليوم / أمس */
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

/* =====================
   الاستعلام
===================== */
$q = "
SELECT 
    p.*, 
    o.invoice_serial, 
    o.supplier_name,
    o.created_at AS order_dating,
    o.vat AS order_vat,
    b.branch_name
FROM purchases p
LEFT JOIN orders_purchases o ON p.order_id = o.id
LEFT JOIN branches b ON b.id = o.branch_id
WHERE 1
";

$params = [];

/* فلترة بالكلمة المفتاحية */
if ($kw !== '') { 
    $q .= " AND p.name LIKE ?"; 
    $params[] = "%$kw%"; 
}

/* فلترة بالفرع */
if (!empty($branch_id) && $branch_id != 0) {
    $q .= " AND o.branch_id = ?";
    $params[] = $branch_id;
}

/* فلترة بالتواريخ */
if ($from_date !== '') {
    $q .= " AND DATE(o.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date !== '') {
    $q .= " AND DATE(o.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY p.id DESC";

$s = $pdo->prepare($q); 
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   تجهيز بيانات الإكسيل
===================== */
$data = [];
$data[] = [
    "ID",
    "الفرع",
    "رقم تسلسلي",
    "البيان",
    "المورد",
    "الوحدة \\ العبوة",
    "الكمية",
    "نوع الوحدة",
    "السعر",
    "الإجمالي قبل الضريبة",
    "الضريبة (15%)",
    "الإجمالي بعد الضريبة",
    "الدافع",
    "مصدر الدفع",
    "التاريخ"
];

foreach ($rows as $r) {

    /* نفس حساباتك تمامًا */
    $quantity = (float)$r['total_packages'];
    $price = (float)$r['total_price'];
    $total = $r['unit_total'];
    $price1 = $r['price'];

    if (!empty($r['order_vat']) && $r['order_vat'] > 0) {
        $vat = $r['unit_vat'];
        $total_with_vat = $r['unit_all_total'];
        $price1 = 0;
    } else {
        $vat = 0;
        $total = $r['unit_all_total'];
        $total_with_vat = $r['unit_all_total'];
        $price1 = $r['price'] + ($r['price'] * 0.15);
    }

    $data[] = [
        $r['id'],
        $r['branch_name'] ?? '-',
        $r['invoice_serial'] ?? '-',
        $r['name'],
        $r['supplier_name'],
        $r['package'],
        $r['quantity'],
        $r['unit'],
        number_format($price1, 7, '.', ''),
        number_format($total, 7, '.', ''),
        number_format($vat, 7, '.', ''),
        number_format($total_with_vat, 7, '.', ''),
        $r['payer_name'],
        $r['payment_source'],
        $r['order_dating']
    ];
}

/* =====================
   سطر توضيح نوع التقرير
===================== */
$header_note = '';
if ($date_type === 'today') {
    $header_note = "تقرير اليوم (" . date('Y-m-d') . ")";
} elseif ($date_type === 'yesterday') {
    $header_note = "تقرير أمس (" . date('Y-m-d', strtotime('-1 day')) . ")";
} elseif ($from_date || $to_date) {
    $header_note = "الفترة من " . ($from_date ?: 'بداية') . " إلى " . ($to_date ?: 'اليوم');
} else {
    $header_note = "كل التقارير";
}

/* إضافته كأول صف */
array_unshift($data, [$header_note]);

/* إنشاء وتنزيل الملف */
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('purchases.xlsx');
?>
