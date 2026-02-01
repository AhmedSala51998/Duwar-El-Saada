<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_permission('reports.report_expenses_pdf');

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$kw        = trim($_GET['kw'] ?? '');
$branch_id = $_GET['branch_id'] ?? '';

$params = [];

// today / yesterday
if ($date_type === 'today') {
    $from_date = $to_date = date('Y-m-d');
} elseif ($date_type === 'yesterday') {
    $from_date = $to_date = date('Y-m-d', strtotime('-1 day'));
}

/* ======================
   الاستعلام
====================== */
$q = "
SELECT e.*, b.branch_name
FROM expenses e
LEFT JOIN branches b ON b.id = e.branch_id
WHERE 1
";

/* كلمة مفتاحية */
if ($kw !== '') {
    $q .= " AND e.main_expense LIKE ?";
    $params[] = "%$kw%";
}

/* فلترة الفرع */
if (!empty($branch_id) && $branch_id != 0) {
    $q .= " AND e.branch_id = ?";
    $params[] = $branch_id;
}

/* فلترة التاريخ */
if ($from_date) {
    $q .= " AND DATE(e.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date) {
    $q .= " AND DATE(e.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY e.id DESC";

$s = $pdo->prepare($q); 
$s->execute($params); 
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>تقرير المصروفات</title>
<style>
body{font-family:Cairo,Arial}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:6px;text-align:center}
th{background:#f7f7f7}
h2{text-align:center;margin-bottom:10px}
tfoot td{font-weight:bold;background:#f1f1f1}
@media print {
  body { font-size: 10px; }
  table { page-break-inside: auto; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  th, td { padding: 3px; }
    #printBtnContainer,
    #printBtnContainer * {
      display: none !important; /* إخفاء الزر وكل محتوياته أثناء الطباعة */
    }
}
table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* مهم */
}

th, td {
  border: 1px solid #ddd;
  padding: 4px;
  text-align: center;
  word-wrap: break-word; /* لتقسيم النصوص الطويلة */
}

</style>
</head>
<body>

<img src="<?= esc(getSystemSettings('secondary_logo') ?: '/assets/logo.png') ?>" width="60" style="float:left">
<h2 style="text-align:center">تقرير المصروفات</h2>

<?php
if ($date_type === 'today') {
    echo "<p style='text-align:center;font-weight:bold'>تقرير اليوم ($from_date)</p>";
} elseif ($date_type === 'yesterday') {
    echo "<p style='text-align:center;font-weight:bold'>تقرير أمس ($from_date)</p>";
} elseif ($from_date || $to_date) {
    echo "<p style='text-align:center;font-weight:bold'>الفترة من ".($from_date ?: 'بداية')." إلى ".($to_date ?: 'اليوم')."</p>";
} else {
    echo "<p style='text-align:center;font-weight:bold'>كل التقرير</p>";
}
?>

<div id="printBtnContainer" style="text-align:center;margin:15px">
<button onclick="window.print()">طباعة التقرير</button>
<button onclick="history.back()">رجوع</button>
</div>

<table>
<thead>
<tr>
<th>#</th>
<th>الفرع</th>
<th>المصروفات</th>
<th>نوع المصروف</th>
<th>بيان المصروف</th>
<th>الإجمالي قبل الضريبة</th>
<th>الضريبة (15%)</th>
<th>الإجمالي بعد الضريبة</th>
<th>الدافع</th>
<th>مصدر الدفع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>

<?php
$totalBefore = $totalVat = $totalAfter = 0;

foreach ($rows as $r):
    /* ==== نفس حساباتك حرفيًا ==== */
    $before = (float)$r['expense_amount'];
    $vat    = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? (float)$r['vat_value'] : 0;
    $after  = (float)$r['total_amount'];
    $before = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? $before : $after;

    $totalBefore += $before;
    $totalVat    += $vat;
    $totalAfter  += $after;
?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['branch_name'] ?? '-') ?></td>
<td><?= esc($r['main_expense']) ?></td>
<td><?= esc($r['sub_expense']) ?></td>
<td><?= esc($r['expense_desc']) ?></td>
<td><?= number_format($before, 4) ?></td>
<td><?= number_format($vat, 4) ?></td>
<td><?= number_format($after, 4) ?></td>
<td><?= esc($r['payer_name'] ?? '-') ?></td>
<td><?= esc($r['payment_source'] ?? '-') ?></td>
<td><?= esc($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
<tfoot>
<tr>
<td colspan="5">الإجماليات</td>
<td><?= number_format($totalBefore, 4) ?></td>
<td><?= number_format($totalVat, 4) ?></td>
<td><?= number_format($totalAfter, 4) ?></td>
<td colspan="3"></td>
</tr>
</tfoot>
</table>

</body>
</html>
