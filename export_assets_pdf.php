<?php require __DIR__.'/config/config.php'; require_auth();
$kw=trim($_GET['kw']??''); $q="SELECT id,name,price,payer_name,created_at FROM assets WHERE 1"; $ps=[]; if($kw!==''){ $q.=" AND name LIKE ?"; $ps[]="%$kw%"; } $q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); $s->execute($ps); $rows=$s->fetchAll();
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><style>body{font-family:Cairo,Arial} table{width:100%;border-collapse:collapse} th,td{border:1px solid #ddd;padding:6px} th{background:#f7f7f7}</style></head>
<body><title>تقرير العُهد</title><img src="assets/logo.svg" width="60" style="float:left"><table><thead><tr><th>#</th><th>الاسم</th><th>السعر</th><th>الدافع</th><th>التاريخ</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= $r['id'] ?></td><td><?= esc($r['name']) ?></td><td><?= number_format((float)$r['price'],2) ?></td><td><?= esc($r['payer_name']) ?></td><td><?= esc($r['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table><script>window.print()</script></body></html>