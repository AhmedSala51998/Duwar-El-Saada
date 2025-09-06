<?php
require __DIR__.'/config/config.php'; 
require_auth();

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM rentals WHERE 1"; 
$ps = [];
if($kw!==''){ $q .= " AND rental_name LIKE ?"; $ps[] = "%$kw%"; }
$q .= " ORDER BY id DESC";

$s = $pdo->prepare($q); 
$s->execute($ps); 
$rows = $s->fetchAll();
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
body{font-family:Cairo,Arial}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:6px}
th{background:#f7f7f7}
</style>
<title>تقرير الإيجارات</title>
</head>
<body>
<h2>تقرير الإيجارات</h2>
<table>
<thead>
<tr>
<th>#</th>
<th>اسم الإيجار</th>
<th>نوع الدفع</th>
<th>السعر</th>
<th>نوع الإيجار</th>
<th>الدافع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['rental_name']) ?></td>
<td><?= esc($r['payment_type']) ?></td>
<td><?= number_format((float)$r['rental_price'],2) ?></td>
<td><?= esc($r['rental_kind']) ?></td>
<td><?= esc($r['payer']) ?></td>
<td><?= esc($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<script>
  window.print();
  window.onafterprint = function () {
    // بيرجع خطوة للخلف
    window.history.back();
  };
</script>
</body>
</html>
