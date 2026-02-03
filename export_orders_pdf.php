<?php 
require __DIR__.'/config/config.php'; 
require_auth();
require_permission('orders.print_pdf');

$kw        = trim($_GET['kw'] ?? '');
$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$branch_id = $_GET['branch_id'] ?? '';   // ✅ استقبال الفرع

$params = [];

/* === منطق التواريخ === */
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

/* === الاستعلام الأساسي مع الربط غير المباشر === */
$q = "
    SELECT 
        o.id,
        p.name AS pname,
        o.qty,
        o.unit,
        o.note,
        o.created_at,
        b.branch_name
    FROM orders o
    JOIN purchases p ON p.id = o.purchase_id
    LEFT JOIN branches b ON b.id = p.branch_id
    WHERE 1
";

/* === البحث (منتج + فرع) === */
if ($kw !== '') {
    $q .= " AND (
        p.name LIKE ?
        OR b.branch_name LIKE ?
    )";
    $params[] = "%$kw%";
    $params[] = "%$kw%";
}

/* === فلترة الفرع === */
if ($branch_id !== '') {
    $q .= " AND p.branch_id = ?";
    $params[] = $branch_id;
}

/* === فلترة التواريخ === */
if ($from_date !== '') {
    $q .= " AND DATE(o.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date !== '') {
    $q .= " AND DATE(o.created_at) <= ?";
    $params[] = $to_date;
}

$q .= " ORDER BY o.id DESC";

/* === التنفيذ === */
$s = $pdo->prepare($q); 
$s->execute($params); 
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
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
@media print {
  body { font-size: 10px; }
  table { page-break-inside: auto; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  th, td { padding: 3px; }
  #printBtnContainer,
  #printBtnContainer * { display: none !important; }
}
</style>
<title>تقرير أوامر التشغيل</title>
</head>
<body>

<h2>تقرير أوامر التشغيل</h2>
<img src="<?= esc(getSystemSettings('secondary_logo') ?: '/assets/logo.png') ?>" width="60" style="float:left">

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

if ($branch_id !== '') {
    echo "<p style='text-align:center;font-weight:bold'>الفرع: " . esc($rows[0]['branch_name'] ?? '') . "</p>";
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
  <th>المنتج</th>
  <th>الفرع</th>
  <th>الكمية</th>
  <th>الوحدة</th>
  <th>ملاحظة</th>
  <th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= esc($r['pname']) ?></td>
  <td><?= esc($r['branch_name'] ?? '—') ?></td>
  <td><?= $r['qty'] ?></td>
  <td><?= esc($r['unit']) ?></td>
  <td><?= esc($r['note']) ?></td>
  <td><?= esc($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<script>
function printAndGoBack() {
  window.print();
  window.onafterprint = () => window.history.back();
}
function goBack() {
  window.history.back();
}
</script>

</body>
</html>
