<?php
require __DIR__.'/config/config.php'; 
require_auth();

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$kw        = trim($_GET['kw'] ?? '');

$params = [];
$dateFilter = '';

if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// فلترة بالكلمة المفتاحية
$q = "SELECT * FROM expenses WHERE 1";
if ($kw !== '') {
    $q .= " AND main_expense LIKE ?";
    $params[] = "%$kw%";
}

// فلترة بالتاريخ
if ($from_date) {
    $q .= " AND DATE(created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date) {
    $q .= " AND DATE(created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q); 
$s->execute($params); 
$rows = $s->fetchAll();
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
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
<title>تقرير المصروفات</title>
</head>
<body>
<h2>تقرير المصروفات</h2>

<?php
if ($date_type === 'today') {
    echo "<p style='text-align:center;font-weight:bold'>تقرير اليوم (" . date('Y-m-d') . ")</p>";
} elseif ($date_type === 'yesterday') {
    echo "<p style='text-align:center;font-weight:bold'>تقرير أمس (" . date('Y-m-d', strtotime('-1 day')) . ")</p>";
} elseif ($from_date || $to_date) {
    $fromText = $from_date ?: 'بداية';
    $toText   = $to_date   ?: 'اليوم';
    echo "<p style='text-align:center;font-weight:bold'>الفترة من $fromText إلى $toText</p>";
} else {
    echo "<p style='text-align:center;font-weight:bold'>كل المصروفات</p>";
}
?>

<table>
<thead>
<tr>
<th>#</th>
<th>المصروفات</th>
<th>نوع المصروف</th>
<th>بيان المصروف</th>
<th>الإجمالي الطبيعي</th>
<th>الضريبة (15%)</th>
<th>الإجمالي بعد الضريبة</th>
<th>المرفق</th>
<th>الدافع</th>
<th>مصدر الدفع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php 
$totalBefore = $totalVat = $totalAfter = 0;
foreach($rows as $r): 
    $before = (float)$r['expense_amount'];
    $vat = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? (float)$r['vat_value'] : 0;
    $after = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? (float)$r['total_amount'] : $before;

    $totalBefore += $before;
    $totalVat += $vat;
    $totalAfter += $after;
?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['main_expense']) ?></td>
<td><?= esc($r['sub_expense']) ?></td>
<td><?= esc($r['expense_desc']) ?></td>
<td><?= $before ?></td>
<td><?= $vat ?></td>
<td><?= $after ?></td>
<td><?= $r['expense_file'] ? esc($r['expense_file']) : '-' ?></td>
<td><?= esc($r['payer_name'] ?? '-') ?></td>
<td><?= esc($r['payment_source'] ?? '-') ?></td>
<td><?= esc($r['created_at'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
<td colspan="4">الإجماليات الكلية</td>
 <td><?= number_format($totalBefore, 4) ?></td>
 <td><?= number_format($totalVat, 4) ?></td>
 <td><?= number_format($totalAfter, 4) ?></td>
<td colspan="4"></td>
</tr>
</tfoot>
</table>

<script>
window.print();
window.onafterprint = function () {
    window.history.back();
};
</script>
</body>
</html>