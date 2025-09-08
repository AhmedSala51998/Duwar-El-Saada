<?php
require __DIR__.'/config/config.php'; 
require_auth();

$kw = trim($_GET['kw'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$q="SELECT * FROM subscriptions WHERE 1"; 
$ps=[];

// فلترة بالكلمة المفتاحية
if($kw !== ''){ 
    $q .= " AND service_name LIKE ?"; 
    $ps[]="%$kw%"; 
}

// فلترة بالتواريخ
if($from_date !== '') {
    $q .= " AND DATE(created_at) >= ?";
    $ps[] = $from_date;
}
if($to_date !== '') {
    $q .= " AND DATE(created_at) <= ?";
    $ps[] = $to_date;
}

$q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); 
$s->execute($ps); 
$rows=$s->fetchAll();
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
</style>
<title>تقرير الاشتراكات والخدمات</title>
</head>
<body>
<h2>تقرير الاشتراكات والخدمات</h2>
<table>
<thead>
<tr>
<th>#</th>
<th>اسم الخدمة</th>
<th>المشتركون</th>
<th>نوع الاشتراك</th>
<th>السعر</th>
<th>الدافع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['service_name']) ?></td>
<td><?= esc($r['subscribers']) ?></td>
<td><?= esc($r['subscription_type']) ?></td>
<td><?= number_format((float)$r['service_price'],2) ?></td>
<td><?= esc($r['payer']) ?></td>
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
