<?php
require __DIR__.'/config/config.php'; 
require_auth();

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q = "SELECT id,name,quantity,unit,price,payer_name,created_at FROM purchases WHERE 1";
$params = [];

// فلترة بالكلمة المفتاحية
if($kw !== '') { 
    $q .= " AND name LIKE ?"; 
    $params[] = "%$kw%"; 
}

// فلترة بالتواريخ
if($from_date !== '') {
    $q .= " AND DATE(created_at) >= ?";
    $params[] = $from_date;
}
if($to_date !== '') {
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تقرير المشتريات</title>
<style>
body{font-family:Cairo,Arial}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:6px;text-align:center}
th{background:#f7f7f7}
.head{display:flex;justify-content:space-between;align-items:center}
h3{margin:0}
</style>
</head>
<body>
<div class="head">
  <div>
    <h3>تقرير المشتريات</h3>
    <div>تم التوليد: <?= date('Y-m-d H:i') ?></div>
  </div>
  <img src="assets/logo.svg" width="60">
</div>
<table>
<thead>
<tr>
<th>#</th>
<th>الاسم</th>
<th>الكمية</th>
<th>الوحدة</th>
<th>السعر</th>
<th>الدافع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= esc($r['name']) ?></td>
  <td><?= $r['quantity'] ?></td>
  <td><?= esc($r['unit']) ?></td>
  <td><?= number_format((float)$r['price'],2) ?></td>
  <td><?= esc($r['payer_name']) ?></td>
  <td><?= esc($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<script>
  window.print();
  window.onafterprint = function () {
    window.history.back();
  };
</script>
</body>
</html>
