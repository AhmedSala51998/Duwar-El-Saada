<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_once __DIR__.'/libs/SimpleXLSXGen.php';
require_permission('reports.report_expenses_excel');

$kw         = trim($_GET['kw'] ?? '');
$date_type  = $_GET['date_type'] ?? '';
$from_date  = $_GET['from_date'] ?? '';
$to_date    = $_GET['to_date'] ?? '';
$branch_id  = $_GET['branch_id'] ?? ''; // فرع

// استعلام قاعدة البيانات
$q  = "SELECT * FROM expenses WHERE 1"; 
$ps = [];

// فلترة بالكلمة المفتاحية
if ($kw !== '') {
    $q .= " AND (main_expense LIKE ? OR sub_expense LIKE ? OR expense_desc LIKE ?)";
    $ps[] = "%$kw%";
    $ps[] = "%$kw%";
    $ps[] = "%$kw%";
}

// منطق الفلترة حسب التاريخ
if ($date_type === 'today') {
    $q .= " AND DATE(created_at) = CURDATE()";
} elseif ($date_type === 'yesterday') {
    $q .= " AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY";
} else {
    if ($from_date !== '') {
        $q .= " AND DATE(created_at) >= ?";
        $ps[] = $from_date;
    }
    if ($to_date !== '') {
        $q .= " AND DATE(created_at) <= ?";
        $ps[] = $to_date;
    }
}

// فلترة بالفرع إذا تم اختياره
if ($branch_id !== '') {
    $q .= " AND branch_id = ?";
    $ps[] = $branch_id;
}

$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q);
$s->execute($ps);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// إعداد بيانات التصدير
$data = [[
    "ID",
    "المصروفات",
    "نوع المصروف",
    "بيان المصروف",
    "الإجمالي قبل الضريبة",
    "الضريبة (15%)",
    "الإجمالي بعد الضريبة",
    "المرفق",
    "الدافع",
    "مصدر الدفع",
    "الفرع",           // إضافة اسم الفرع
    "التاريخ"
]];

foreach ($rows as $r) {
    $before = (float)$r['expense_amount'];
    $vat    = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? (float)$r['vat_value'] : 0;
    $after  = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? (float)$r['total_amount'] : (float)$r['total_amount'];
    $before  = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? $before : (float)$r['total_amount'];

    $main = $r['main_expense'] ?: '-';
    $sub  = ($r['sub_expense'] === 'أخرى' || empty($r['sub_expense'])) 
              ? $r['expense_desc'] 
              : $r['sub_expense'];

    // جلب اسم الفرع
    $branch_name = '-';
    if (!empty($r['branch_id'])) {
        $b = $pdo->prepare("SELECT branch_name FROM branches WHERE id=?");
        $b->execute([$r['branch_id']]);
        $branch_name = $b->fetchColumn() ?: '-';
    }

    $data[] = [
        $r['id'],
        $main,
        $sub,
        $r['expense_desc'] ?? '-',
        number_format($before, 7, '.', ''),
        number_format($vat, 7, '.', ''),
        number_format($after, 7, '.', ''),
        $r['expense_file'] ?? '-',
        $r['payer_name'] ?? '',
        $r['payment_source'] ?? '',
        $branch_name,
        $r['created_at'] ?? ''
    ];
}

// إنشاء ملف Excel وتنزيله
Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('expenses.xlsx');
?>