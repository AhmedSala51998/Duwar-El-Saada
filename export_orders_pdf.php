<?php require __DIR__.'/config/config.php'; require_auth();
$kw=trim($_GET['kw']??''); $q="SELECT o.id, p.name pname, o.qty, o.unit, o.note, o.created_at FROM orders o JOIN purchases p ON p.id=o.purchase_id WHERE 1"; $ps=[];
if($kw!==''){ $q.=" AND p.name LIKE ?"; $ps[]="%$kw%"; } $q.=" ORDER BY o.id DESC";
$s=$pdo->prepare($q); $s->execute($ps); $rows=$s->fetchAll();
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><style>body{font-family:Cairo,Arial} table{width:100%;border-collapse:collapse} th,td{border:1px solid #ddd;padding:6px} th{background:#f7f7f7}</style></head>
<body><title>تقرير أوامر التشغيل</title><img src="assets/logo.svg" width="60" style="float:left"><table><thead><tr><th>#</th><th>المنتج</th><th>الكمية</th><th>الوحدة</th><th>ملاحظة</th><th>التاريخ</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= $r['id'] ?></td><td><?= esc($r['pname']) ?></td><td><?= $r['qty'] ?></td><td><?= esc($r['unit']) ?></td><td><?= esc($r['note']) ?></td><td><?= esc($r['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table><script>window.print()</script></body></html>