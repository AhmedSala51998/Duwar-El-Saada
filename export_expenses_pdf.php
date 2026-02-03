<?php
require __DIR__.'/config/config.php'; 
require_auth();
require_permission('reports.report_expenses_pdf');

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$kw        = trim($_GET['kw'] ?? '');
$branch_id = $_GET['branch_id'] ?? '';

$params = [];
$dateFilter = '';

if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

/* =====================
   الاستعلام
===================== */
$q = "
SELECT e.*, b.branch_name
FROM expenses e
LEFT JOIN branches b ON b.id = e.branch_id
WHERE 1
";

/* فلترة بالكلمة المفتاحية */
if ($kw !== '') {
    $q .= " AND e.main_expense LIKE ?";
    $params[] = "%$kw%";
}

/* فلترة الفرع */
if (!empty($branch_id) && $branch_id != 0) {
    $q .= " AND e.branch_id = ?";
    $params[] = $branch_id;
}

/* فلترة بالتاريخ */
if ($from_date) {
    $q .= " AND DATE(e.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date) {
    $q .= " AND DATE(e.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY e.id DESC";

$s = $pdo->prepare($q); 
$s->execute($params); 
$rows = $s->fetchAll();
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
tfoot td{font-weight:bold;background:#f1f1f1}
@media print {
  body { font-size: 10px; }
  table { page-break-inside: auto; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  th, td { padding: 3px; }
  #printBtnContainer,
  #printBtnContainer * {
    display: none !important;
  }
}
table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}
th, td {
  border: 1px solid #ddd;
  padding: 4px;
  text-align: center;
  word-wrap: break-word;
}
</style>
<title>تقرير المصروفات</title>
</head>
<body>

<img src="<?= esc(getSystemSettings('secondary_logo') ?: '/assets/logo.png') ?>" width="60" style="float:left">
<h2>تقرير المصروفات</h2>

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

<div id="printBtnContainer" style="text-align:center;margin:20px 0; display:flex; gap:15px; justify-content:center">
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
<th>الفرع</th>
<th>المصروفات</th>
<th>نوع المصروف</th>
<th>بيان المصروف</th>
<th>الإجمالي الطبيعي</th>
<th>الضريبة (15%)</th>
<th>الإجمالي بعد الضريبة</th>
<th>المرفق</th>
<th>الدافع</th>
<th>مصدر الدفع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>

<?php 
$totalBefore = $totalVat = $totalAfter = 0;

foreach($rows as $r): 
    /* نفس حساباتك تمامًا */
    $before = (float)$r['expense_amount'];
    $vat = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? (float)$r['vat_value'] : 0;
    $after = (float)$r['total_amount'];
    $before = (!empty($r['has_vat']) && $r['has_vat'] == 1) ? $before : $after;

    $totalBefore += $before;
    $totalVat += $vat;
    $totalAfter += $after;
?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['branch_name'] ?? '-') ?></td>
<td><?= esc($r['main_expense']) ?></td>
<td><?= esc($r['sub_expense']) ?></td>
<td><?= esc($r['expense_desc']) ?></td>
<td><?= $before ?></td>
<td><?= $vat ?></td>
<td><?= $after ?></td>
<td><?= $r['expense_file'] ? esc($r['expense_file']) : '-' ?></td>
<td><?= esc($r['payer_name'] ?? '-') ?></td>
<td><?= esc($r['payment_source'] ?? '-') ?></td>
<td><?= esc($r['created_at'] ?? '') ?></td>
</tr>
<?php endforeach; ?>

</tbody>
<tfoot>
<?php if(!empty($r['has_vat']) && $r['has_vat'] == 1){ ?>    
<tr>
<td colspan="5">الإجماليات الكلية</td>
<td><?= number_format($totalBefore, 4) ?></td>
<td><?= number_format($totalVat, 4) ?></td>
<td><?= number_format($totalAfter, 4) ?></td>
<td colspan="4"></td>
</tr>
<?php }else{ ?>   
<tr>
<td colspan="5">الإجماليات الكلية</td>
<td><?= number_format($totalBefore, 4) ?></td>
<td>------</td>
<td><?= number_format($totalAfter, 4) ?></td>
<td colspan="4"></td>
</tr>
<?php } ?>   
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
