<?php
require __DIR__.'/config/config.php';
require_auth();

$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$dateFilter = '';
$params = [];
if($from_date) { $dateFilter .= " AND DATE(created_at) >= ?"; $params[] = $from_date; }
if($to_date)   { $dateFilter .= " AND DATE(created_at) <= ?"; $params[] = $to_date; }

function renderSection($title, $rows, $columns, &$totalBefore, &$totalVat, &$totalAfter) {
    echo "<h4 style='margin-top:20px'>$title</h4>";
    echo "<table><thead><tr>";
    foreach($columns as $col) echo "<th>$col</th>";
    echo "</tr></thead><tbody>";

    $sectionBefore = 0; $sectionVat = 0; $sectionAfter = 0;

    foreach($rows as $r){
        echo "<tr>";
        echo "<td>".htmlspecialchars($r['name'])."</td>";
        echo "<td>".number_format($r['before'],2)."</td>";
        echo "<td>".number_format($r['vat'],2)."</td>";
        echo "<td>".number_format($r['after'],2)."</td>";
        echo "</tr>";

        $sectionBefore += $r['before'];
        $sectionVat    += $r['vat'];
        $sectionAfter  += $r['after'];
    }

    echo "<tr style='font-weight:bold;background:#f1f1f1'>
            <td>الإجمالي الفرعي</td>
            <td>".number_format($sectionBefore,2)."</td>
            <td>".number_format($sectionVat,2)."</td>
            <td>".number_format($sectionAfter,2)."</td>
          </tr></tbody></table>";

    $totalBefore += $sectionBefore;
    $totalVat    += $sectionVat;
    $totalAfter  += $sectionAfter;
}

// ---------------------------- المشتريات ----------------------------
// ---------------------------- المشتريات (تفصيل المنتجات) ----------------------------
$stmt = $pdo->prepare("SELECT 
                          name,
                          (price * quantity) AS before,
                          (price * quantity * 0.15) AS vat,
                          (price * quantity * 1.15) AS after
                       FROM orders_purchases
                       WHERE 1=1 $dateFilter");
$stmt->execute($params);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- المصروفات ----------------------------
// ---------------------------- المصروفات ----------------------------
$stmt = $pdo->prepare("SELECT 
                              CONCAT(main_expense, ' - ', sub_expense) AS name, 
                              expense_amount AS before, 
                              (CASE WHEN has_vat=1 THEN expense_amount*0.15 ELSE 0 END) AS vat, 
                              (CASE WHEN has_vat=1 THEN expense_amount*1.15 ELSE expense_amount END) AS after 
                       FROM expenses 
                       WHERE 1=1 $dateFilter");
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- الأصول ----------------------------
$stmt = $pdo->prepare("SELECT name, (price*quantity) AS before, 
                              (CASE WHEN has_vat=1 THEN price*quantity*0.15 ELSE 0 END) AS vat, 
                              (CASE WHEN has_vat=1 THEN price*quantity*1.15 ELSE price*quantity END) AS after 
                       FROM assets WHERE 1=1 $dateFilter");
$stmt->execute($params);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- تجميع الكل ----------------------------
$totalBefore = 0; $totalVat = 0; $totalAfter = 0;
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>تقرير تفصيلي للضريبة</title>
<style>
  body{font-family:Cairo,Arial;margin:20px}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  th,td{border:1px solid #ddd;padding:6px;text-align:center}
  th{background:#f7f7f7}
  h3,h4{text-align:center}
</style>
</head>
<body>
<h3>تقرير تفصيلي للضريبة</h3>

<?php
renderSection("المشتريات", $purchases, ['الاسم','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
renderSection("المصروفات", $expenses, ['الاسم','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
renderSection("الأصول", $assets, ['الأصل','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
?>

<table style="margin-top:30px;font-weight:bold;background:#d4edda">
  <tr>
    <td>الإجمالي الكلي قبل الضريبة</td>
    <td><?= number_format($totalBefore,2) ?></td>
  </tr>
  <tr>
    <td>إجمالي الضريبة</td>
    <td><?= number_format($totalVat,2) ?></td>
  </tr>
  <tr>
    <td>الإجمالي الكلي بعد الضريبة</td>
    <td><?= number_format($totalAfter,2) ?></td>
  </tr>
</table>

<script>
  window.print();
  window.onafterprint = function(){ window.history.back(); }
</script>
</body>
</html>
