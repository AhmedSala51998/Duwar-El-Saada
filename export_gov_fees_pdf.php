<?php
require __DIR__.'/config/config.php'; 
require_auth();

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM gov_fees WHERE 1"; 
$ps = [];
if($kw!==''){ $q .= " AND fee_title LIKE ?"; $ps[] = "%$kw%"; }
$q .= " ORDER BY id DESC";
$s = $pdo->prepare($q); $s->execute($ps); $rows = $s->fetchAll();
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
<title>تقرير الرسوم الحكومية</title>
</head>
<body>
<h2>تقرير الرسوم الحكومية</h2>
<table>
<thead>
<tr>
<th>#</th>
<th>عنوان الرسوم</th>
<th>نوع الرسوم</th>
<th>السعر</th>
<th>الدافع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['fee_title']) ?></td>
<td><?= esc($r['fee_type']) ?></td>
<td><?= number_format((float)$r['fee_amount'],2) ?></td>
<td><?= esc($r['payer']) ?></td>
<td><?= esc($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<script>window.print()</script>
</body>
</html>
