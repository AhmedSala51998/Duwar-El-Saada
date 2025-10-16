<?php 
require __DIR__.'/config/config.php'; 
require_auth();

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
$q = "SELECT id, person_name, amount, taken_at, notes, created_at FROM custodies WHERE 1";

// فلترة بالكلمة المفتاحية
if ($kw !== '') { 
    $q .= " AND person_name LIKE ?"; 
    $params[] = "%$kw%"; 
}

// فلترة بالتواريخ
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
<title>تقرير العُهد</title>
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
table { table-layout: fixed; }
th, td { word-wrap: break-word; }
</style>
</head>
<body>
<img src="assets/logo.png" width="60" style="float:left">
<h2>تقرير العُهد</h2>

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
    echo "<p style='text-align:center;font-weight:bold'>كل التقرير</p>";
}
?>

<table>
<thead>
<tr>
  <th>#</th>
  <th>الشخص</th>
  <th>المبلغ</th>
  <th>تاريخ الاستلام</th>
  <th>ملاحظات</th>
  <th>تاريخ الإضافة</th>
</tr>
</thead>
<tbody>
<?php 
$totalAmount = 0;
foreach($rows as $r): 
    $amount = (float)$r['main_amount'];
    $totalAmount += $amount;
?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= htmlspecialchars($r['person_name']) ?></td>
  <td><?= number_format($amount,2) ?></td>
  <td><?= htmlspecialchars($r['taken_at']) ?></td>
  <td><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
  <td><?= htmlspecialchars($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
  <td colspan="2">الإجمالي الكلي</td>
  <td><?= number_format($totalAmount, 2) ?></td>
  <td colspan="3"></td>
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
