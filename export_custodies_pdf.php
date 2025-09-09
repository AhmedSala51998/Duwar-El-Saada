<?php 
require __DIR__.'/config/config.php'; 
require_auth();

$kw = trim($_GET['kw'] ?? ''); 
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q = "SELECT id, person_name, amount, taken_at, notes, created_at FROM custodies WHERE 1"; 
$ps = []; 

// فلترة بالكلمة المفتاحية
if ($kw !== '') { 
  $q .= " AND person_name LIKE ?"; 
  $ps[] = "%$kw%"; 
} 

// فلترة بالتواريخ
if ($from_date !== '') {
  $q .= " AND DATE(created_at) >= ?";
  $ps[] = $from_date;
}
if ($to_date !== '') {
  $q .= " AND DATE(created_at) <= ?";
  $ps[] = $to_date;
}

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
<title>تقرير العُهد</title>
</head>
<body>
<img src="assets/logo.svg" width="60" style="float:left">
<h3>تقرير العُهد</h3>
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
<?php foreach($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= esc($r['person_name']) ?></td>
  <td><?= number_format((float)$r['amount'],2) ?></td>
  <td><?= esc($r['taken_at']) ?></td>
  <td><?= esc($r['notes']) ?></td>
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
