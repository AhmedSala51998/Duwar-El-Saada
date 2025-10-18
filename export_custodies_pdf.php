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

// استعلام العهد
$q = "SELECT id, person_name, main_amount, amount, taken_at, notes, created_at FROM custodies WHERE 1";

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

$q .= " ORDER BY id ASC";

$s = $pdo->prepare($q); 
$s->execute($params); 
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

// إعداد استعلام الحركات
$transactions_stmt = $pdo->prepare("SELECT * FROM custody_transactions WHERE custody_id=? ORDER BY created_at ASC");

$last_balance = 0; // الرصيد التراكمي
$total_in = 0;
$total_out = 0;
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
.table-primary { background-color: #d9edf7; }
</style>
</head>
<body>
<img src="assets/logo.png" width="60" style="float:left">
<h2>تقرير العُهد</h2>

<table>
<thead>
<tr>
  <th>#</th>
  <th>الشخص</th>
  <th>الوارد</th>
  <th>الصادر</th>
  <th>الرصيد</th>
  <th>تاريخ الاستلام</th>
  <th>ملاحظات</th>
  <th>تاريخ الإضافة</th>
</tr>
</thead>
<tbody>
<?php 
foreach($rows as $r): 

    // جلب الحركات المرتبطة بالعهدة
    $transactions_stmt->execute([$r['id']]);
    $transactions = $transactions_stmt->fetchAll(PDO::FETCH_ASSOC);

    $in = (float)$r['main_amount'];  // الوارد
    $remain = (float)$r['sub_amount'];   // المتبقي
    $out = $in - $remain;            // المصروف
    if($out < 0) $out = 0;

    // الرصيد = الرصيد السابق + الوارد - الصادر
    $current_balance = $last_balance + $in - $out;
    $last_balance = $current_balance;

    $total_in  += $in;
    $total_out += $out;
?>
<tr class="table-primary">
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['person_name']) ?></td>
    <td><?= number_format($in,2) ?></td>
    <td><?= number_format($out,2) ?></td>
    <td><?= number_format($current_balance,2) ?></td>
    <td><?= htmlspecialchars($r['taken_at']) ?></td>
    <td><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
    <td><?= htmlspecialchars($r['created_at']) ?></td>
</tr>

<?php
    // طباعة الحركات إذا موجودة
    $prev_balance = $current_balance;
    foreach($transactions as $t):
        $trans_amount = (float)$t['amount'];

        // خصم الصرف من الرصيد
        $current_balance = $prev_balance - $trans_amount;

        // تحويل النوع للعربي
        $type_ar = '';
        switch($t['type']) {
            case 'asset': $type_ar = 'أصول'; break;
            case 'expense': $type_ar = 'مصروفات'; break;
            case 'purchase': $type_ar = 'مشتريات'; break;
            default: $type_ar = htmlspecialchars($t['type']); 
        }
?>
<tr>
    <td></td>
    <td>-- <?= $type_ar ?></td>
    <td></td>
    <td><?= number_format($trans_amount,2) ?></td>
    <td><?= number_format($current_balance,2) ?></td>
    <td><?= htmlspecialchars($t['created_at']) ?></td>
    <td><?= htmlspecialchars($t['notes'] ?? '') ?></td>
    <td>حركة</td>
</tr>
<?php
        $prev_balance = $current_balance;
    endforeach; 
endforeach; 
?>
</tbody>
<tfoot>
<tr>
    <td colspan="2">الإجماليات</td>
    <td><?= number_format($total_in, 2) ?></td>
    <td><?= number_format($total_out, 2) ?></td>
    <td><?= number_format($last_balance, 2) ?></td>
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
