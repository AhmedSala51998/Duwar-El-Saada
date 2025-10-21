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
$q = "SELECT id, person_name, main_amount,sub_amount, amount, taken_at, notes, created_at FROM custodies WHERE 1";

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

$q .= " ORDER BY taken_at ASC";

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
    #printBtnContainer,
    #printBtnContainer * {
      display: none !important; /* إخفاء الزر وكل محتوياته أثناء الطباعة */
    }
}
table { table-layout: fixed; }
th, td { word-wrap: break-word; }
.table-primary { background-color: #d9edf7; }
</style>
</head>
<body>
<img src="assets/logo.png" width="60" style="float:left">
<h2>تقرير العُهد</h2>
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

<div id="printBtnContainer" style="text-align:center;margin:20px 0; display: flex; justify-content: center; gap: 15px;">
  <!-- زر الطباعة -->
  <button 
    onclick="printAndGoBack()" 
    style="
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
    "
    onmouseover="this.style.backgroundColor='#45a049';"
    onmouseout="this.style.backgroundColor='#4CAF50';"
  >
    طباعة التقرير
  </button>

  <!-- زر الرجوع -->
  <button 
    onclick="goBack()" 
    style="
      background-color: #f44336;  /* أحمر */
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
    "
    onmouseover="this.style.backgroundColor='#d32f2f';"
    onmouseout="this.style.backgroundColor='#f44336';"
  >
    العودة للصفحة السابقة
  </button>
</div>

<table>
<thead>
<tr>
  <th>#</th>
  <th>البيان</th>
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
    $in = (float)$r['main_amount'];  // الوارد
    $remain = (float)$r['sub_amount'];   // المتبقي
    $out = $in - $remain;            // المصروف
    if($out < 0) $out = 0;

    // جلب الحركات المرتبطة بالعهدة
    $transactions_stmt->execute([$r['id']]);
    $transactions = $transactions_stmt->fetchAll();

    if(count($transactions) > 0){
        // لو فيه حركة، الرصيد يبدأ من الوارد - الصادر
        $current_balance = $in - $out;
    } else {
        // لو مفيش حركة، الرصيد يعتمد على آخر رصيد محسوب
        $current_balance = $last_balance + $in - $out;
    }

    // تحديث الرصيد الأخير للصفوف التالية
    $last_balance = $current_balance;
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
// استعراض الحركات
foreach($transactions as $t):
    $trans_amount = (float)$t['amount'];

    // خصم الحركة من الرصيد الحالي
    $current_balance -= $trans_amount;

    // تحديث آخر رصيد بعد كل حركة
    $last_balance = $current_balance;

    $type_ar = '';
    switch($t['type']) {
        case 'asset': $type_ar = 'أصول'; break;
        case 'expense': $type_ar = 'مصروفات'; break;
        case 'purchase': $type_ar = 'مشتريات'; break;
        default: $type_ar = esc($t['type']); 
    }
?>
<tr>
    <td></td>
    <td><?= htmlspecialchars($r['person_name']) ?> -- <?= $type_ar ?></td>
    <td></td>
    <td><?= number_format($trans_amount,2) ?></td>
    <td><?= number_format($current_balance,2) ?></td>
    <td><?= htmlspecialchars($t['created_at']) ?></td>
    <td><?= htmlspecialchars($t['notes'] ?? '') ?></td>
    <td><?= $type_ar ?></td>
</tr>
<?php
        $prev_balance = $current_balance;
    endforeach; 
endforeach; 
?>
</tbody>
<tfoot>
<?php 
$last_balance = 0; // الرصيد السابق
$total_in = 0; 
$total_out = 0; 

foreach($rows as $r) {
  $total_in += (float)$r['main_amount'];
  $total_out += ((float)$r['main_amount'] - (float)$r['amount']);
}
$total_balance = $total_in - $total_out;
?>
<tr>
    <td colspan="2">الإجماليات</td>
    <td><?= number_format($total_in, 2) ?></td>
    <td><?= number_format($total_out, 2) ?></td>
    <td><?= number_format($total_balance, 2) ?></td>
    <td colspan="3"></td>
</tr>
</tfoot>
</table>

<script>
function printAndGoBack() {
  window.print();
  window.onafterprint = function () {
    window.history.back();
  };
}
function goBack() {
  window.history.back();
}
</script>
</body>
</html>
