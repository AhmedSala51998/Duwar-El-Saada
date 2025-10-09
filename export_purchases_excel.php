<?php
require __DIR__.'/config/config.php';
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';

$kw        = trim($_GET['kw'] ?? '');
$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

// منطق اليوم / أمس / من تاريخ لتاريخ
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

$q = "SELECT 
        p.*, 
        o.invoice_serial, 
        o.supplier_name,
        o.vat AS order_vat
      FROM purchases p
      LEFT JOIN orders_purchases o ON p.order_id = o.id
      WHERE 1";

$params = [];

// فلترة بالكلمة المفتاحية
if($kw !== '') { 
    $q .= " AND p.name LIKE ?"; 
    $params[] = "%$kw%"; 
}

// فلترة بالتواريخ
if($from_date !== '') {
    $q .= " AND DATE(p.created_at) >= ?";
    $params[] = $from_date;
}
if($to_date !== '') {
    $q .= " AND DATE(p.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY p.id DESC";

$s = $pdo->prepare($q); 
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// تجهيز البيانات للتصدير
$data = [];
$data[] = [
    "ID",
    "رقم تسلسلي",
    "الاسم",
    "المورد",
    "العبوة",
    "الكمية",
    "الوحدة",
    "السعر",
    "الإجمالي قبل الضريبة",
    "الضريبة (15%)",
    "الإجمالي بعد الضريبة",
    "الدافع",
    "مصدر الدفع",
    "التاريخ"
];

foreach ($rows as $r) {
    $quantity = (float)$r['quantity'];
    $price = (float)$r['price'];
    $total = $quantity * $price;

    // تحديد هل الفاتورة فيها ضريبة أم لا
    if (!empty($r['order_vat']) && $r['order_vat'] > 0) {
        $vat = $total * 0.15;
        $total_with_vat = $total + $vat;
    } else {
        $vat = 0;
        $total_with_vat = $total;
    }

    $data[] = [
        $r['id'],
        $r['invoice_serial'] ?? '-',
        $r['name'],
        $r['supplier_name'],
        $r['package'],
        $quantity,
        $r['unit'],
        number_format($price, 7, '.', ''),
        number_format($total, 7, '.', ''),
        number_format($vat, 7, '.', ''),
        number_format($total_with_vat, 7, '.', ''),
        $r['payer_name'],
        $r['payment_source'],
        $r['created_at']
    ];
}

// إضافة صف في أول الملف لتوضيح نوع التقرير (اليوم / أمس / فترة محددة)
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

// نضيفه كصف أول في الإكسيل
array_unshift($data, [$header_note]);

// إنشاء ملف Excel وتنزيله
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('purchases.xlsx');
?>
