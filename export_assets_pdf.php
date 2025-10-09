<?php 
require __DIR__.'/config/config.php'; 
require_auth();

$kw         = trim($_GET['kw'] ?? ''); 
$date_type  = $_GET['date_type'] ?? '';
$from_date  = $_GET['from_date'] ?? '';
$to_date    = $_GET['to_date'] ?? '';

$params = [];
$dateFilter = '';

// تطبيق منطق الفلترة حسب نوع التاريخ
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// فلترة بالبحث بالكلمة
$q = "SELECT id,name,type,quantity,price,has_vat,payer_name,payment_source,created_at FROM assets WHERE 1";

if ($kw !== '') { 
  $q .= " AND name LIKE ?"; 
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
<title>تقرير الأصول</title>
<style>
  body{font-family:Cairo,Arial}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #ddd;padding:6px;text-align:center}
  th{background:#f7f7f7}
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
</head>
<body>
<img src="assets/logo.svg" width="60" style="float:left">
<h2 style="text-align:center;margin:0">تقرير الأصول</h2>

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
  <th>الاسم</th>
  <th>النوع</th>
  <th>العدد</th>
  <th>السعر</th>
  <th>الإجمالي الطبيعي</th>
  <th>الضريبة (15%)</th>
  <th>الإجمالي بعد الضريبة</th>
  <th>الدافع</th>
  <th>مصدر الدفع</th>
  <th>التاريخ</th>
</tr>
</thead>
<?php 
$totalBefore = $totalVat = $totalAfter = 0;
?>
<tbody>
<?php foreach($rows as $r): 
    $quantity = (float)$r['quantity'];
    $price = (float)$r['price'];
    $total = $quantity * $price;

    if(!empty($r['has_vat']) && $r['has_vat'] == 1){
        $vat = $total * 0.15;
        $total_with_vat = $total + $vat;
    } else {
        $vat = 0;
        $total_with_vat = $total;
    }

    $totalBefore += $total;
    $totalVat += $vat;
    $totalAfter += $total_with_vat;
?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= htmlspecialchars($r['name']) ?></td>
  <td><?= htmlspecialchars($r['type']) ?></td>
  <td><?= $quantity ?></td>
  <td><?= number_format($price, 7) ?></td>
  <td><?= number_format($total, 7) ?></td>
  <td><?= number_format($vat, 7) ?></td>
  <td><?= number_format($total_with_vat, 7) ?></td>
  <td><?= htmlspecialchars($r['payer_name']) ?></td>
  <td><?= htmlspecialchars($r['payment_source'] ?? '-') ?></td>
  <td><?= htmlspecialchars($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>

<tfoot>
<tr style="font-weight:bold;background:#f1f1f1">
  <td colspan="5">الإجماليات الكلية</td>
  <td><?= number_format($totalBefore, 7) ?></td>
  <td><?= number_format($totalVat, 7) ?></td>
  <td><?= number_format($totalAfter, 7) ?></td>
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