<?php
require __DIR__.'/config/config.php';
require_auth();

$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$dateFilter = '';
$params = [];
if($from_date) { $dateFilter .= " AND DATE(created_at) >= ?"; $params[] = $from_date; }
if($to_date)   { $dateFilter .= " AND DATE(created_at) <= ?"; $params[] = $to_date; }

// ضريبة المشتريات
$stmt = $pdo->prepare("SELECT SUM(vat) as total_vat FROM orders_purchases WHERE 1=1 $dateFilter");
$stmt->execute($params);
$purchase_vat = $stmt->fetchColumn() ?: 0;

// ضريبة المصروفات
$stmt = $pdo->prepare("SELECT SUM(expense_amount*0.15) as total_vat FROM expenses WHERE 1=1 $dateFilter AND has_vat=1");
$stmt->execute($params);
$expenses_vat = $stmt->fetchColumn() ?: 0;

// ضريبة الأصول
$stmt = $pdo->prepare("SELECT SUM(price*quantity*0.15) as total_vat FROM assets WHERE 1=1 $dateFilter AND has_vat=1");
$stmt->execute($params);
$assets_vat = $stmt->fetchColumn() ?: 0;

$total_vat = $purchase_vat + $expenses_vat + $assets_vat;
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
  body{font-family:Cairo,Arial}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  th,td{border:1px solid #ddd;padding:6px;text-align:center}
  th{background:#f7f7f7}
</style>
<title>تقرير تقريب الضريبة</title>
</head>
<body>
<h3>تقرير تقريب الضريبة</h3>
<table>
<thead>
<tr>
  <th>المصدر</th>
  <th>مجموع الضريبة</th>
</tr>
</thead>
<tbody>
<tr><td>المشتريات</td><td><?= number_format($purchase_vat,2) ?></td></tr>
<tr><td>المصروفات</td><td><?= number_format($expenses_vat,2) ?></td></tr>
<tr><td>الأصول</td><td><?= number_format($assets_vat,2) ?></td></tr>
<tr style="font-weight:bold;background:#d4edda"><td>المجموع الكلي</td><td><?= number_format($total_vat,2) ?></td></tr>
</tbody>
</table>
<script>
  window.print();
  window.onafterprint = function(){ window.history.back(); }
</script>
</body>
</html>
