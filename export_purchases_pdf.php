<?php
require __DIR__.'/config/config.php'; 
require_auth();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// === جلب المتغيرات ===
$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$kw        = trim($_GET['kw'] ?? '');

// === تحديد نوع التقرير (اليوم / أمس / من إلى) ===
if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// === بناء الاستعلام ===
$q = "SELECT 
        p.*, 
        o.invoice_serial, 
        o.supplier_name,
        o.vat AS order_vat
      FROM purchases p
      LEFT JOIN orders_purchases o ON p.order_id = o.id
      WHERE 1=1";

$params = [];

// فلترة بالكلمة المفتاحية
if($kw !== '') { 
    $q .= " AND p.name LIKE ?"; 
    $params[] = "%$kw%"; 
}

// فلترة حسب التاريخ
if ($from_date) { 
    $q .= " AND DATE(p.created_at) >= ?"; 
    $params[] = $from_date; 
}
if ($to_date) { 
    $q .= " AND DATE(p.created_at) <= ?"; 
    $params[] = $to_date; 
}

$q .= " ORDER BY p.id DESC";

$s = $pdo->prepare($q); 
$s->execute($params);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تقرير المشتريات</title>
<style>
body{font-family:Cairo,Arial}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:6px;text-align:center}
th{background:#f7f7f7}
.head{display:flex;justify-content:space-between;align-items:center}
h3{margin:0}
tfoot td{font-weight:bold;background:#f1f1f1}
@media print {
  body { font-size: 10px; }
  table { page-break-inside: auto; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  th, td { padding: 3px; }
}
table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* مهم */
}

th, td {
  border: 1px solid #ddd;
  padding: 4px;
  text-align: center;
  word-wrap: break-word; /* لتقسيم النصوص الطويلة */
}

</style>
</head>
<body>
<div class="head">
  <div>
    <h3>تقرير المشتريات</h3>
    <div>تم التوليد: <?= date('Y-m-d H:i') ?></div>
    <?php
    if ($date_type === 'today') {
        echo "<div>تقرير اليوم (" . date('Y-m-d') . ")</div>";
    } elseif ($date_type === 'yesterday') {
        echo "<div>تقرير أمس (" . date('Y-m-d', strtotime('-1 day')) . ")</div>";
    } elseif ($from_date || $to_date) {
        $fromText = $from_date ?: 'بداية';
        $toText   = $to_date   ?: 'اليوم';
        echo "<div>الفترة من $fromText إلى $toText</div>";
    } else {
        echo "<div>كل التقرير</div>";
    }
    ?>
  </div>
  <img src="assets/logo.svg" width="60">
</div>

<table>
<thead>
<tr>
<th>#</th>
<th>رقم تسلسلي</th>
<th>الاسم</th>
<th>المورد</th>
<th>الكمية</th>
<th>الوحدة</th>
<th>السعر</th>
<th>الإجمالي قبل الضريبة</th>
<th>الضريبة (15%)</th>
<th>الإجمالي بعد الضريبة</th>
<th>الدافع</th>
<th>مصدر الدفع</th>
<th>التاريخ</th>
</tr>
</thead>
<tbody>
<?php 
$totalBefore = $totalVat = $totalAfter = 0;

foreach($rows as $r): 
  $before = $r['quantity'] * $r['price'];

  // لو الفاتورة فيها ضريبة فعلية نحسب الضريبة للمنتجات
  if (!empty($r['order_vat']) && $r['order_vat'] > 0) {
      $vat  = $before * 0.15;
      $after = $before + $vat;
  } else {
      // مافيش ضريبة على الفاتورة، نخلي الضريبة صفر
      $vat = 0;
      $after = $before;
  }

  $totalBefore += $before;
  $totalVat += $vat;
  $totalAfter += $after;
?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= esc($r['invoice_serial'] ?? '-') ?></td>
  <td><?= esc($r['name']) ?></td>
  <td><?= esc($r['supplier_name']) ?></td>
  <td><?= $r['quantity'] ?></td>
  <td><?= esc($r['unit']) ?></td>
  <td><?= number_format((float)$r['price'], 7) ?></td>
  <td><?= number_format($before, 7) ?></td>
  <td><?= number_format($vat, 7) ?></td>
  <td><?= number_format($after, 7) ?></td>
  <td><?= esc($r['payer_name']) ?></td>
  <td><?= esc($r['payment_source']) ?></td>
  <td><?= esc($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
  <td colspan="7">الإجماليات الكلية</td>
  <td><?= number_format($totalBefore, 2) ?></td>
  <td><?= number_format($totalVat, 2) ?></td>
  <td><?= number_format($totalAfter, 2) ?></td>
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
