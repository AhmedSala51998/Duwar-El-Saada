<?php
require __DIR__.'/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_auth();

$date_type = $_GET['date_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$paramsPurchases = [];
$paramsAssets    = [];
$paramsExpenses  = [];

$dateFilterPurchases = '';
$dateFilterAssets    = '';
$dateFilterExpenses  = '';

if ($date_type === 'today') {
    $today = date('Y-m-d');
    $from_date = $to_date = $today;
} elseif ($date_type === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $from_date = $to_date = $yesterday;
}

// فلترة المشتريات (p.created_at)
if($from_date) { 
    $dateFilterPurchases .= " AND DATE(o.created_at) >= ?"; 
    $paramsPurchases[] = $from_date; 
}
if($to_date) { 
    $dateFilterPurchases .= " AND DATE(o.created_at) <= ?"; 
    $paramsPurchases[] = $to_date; 
}

// فلترة الأصول (a.created_at)
if($from_date) { 
    $dateFilterAssets .= " AND DATE(a.created_at) >= ?"; 
    $paramsAssets[] = $from_date; 
}
if($to_date) { 
    $dateFilterAssets .= " AND DATE(a.created_at) <= ?"; 
    $paramsAssets[] = $to_date; 
}

// فلترة المصروفات (e.created_at)
if($from_date) { 
    $dateFilterExpenses .= " AND DATE(e.created_at) >= ?"; 
    $paramsExpenses[] = $from_date; 
}
if($to_date) { 
    $dateFilterExpenses .= " AND DATE(e.created_at) <= ?"; 
    $paramsExpenses[] = $to_date; 
}

function renderSection($title, $rows, $columns, &$totalBefore, &$totalVat, &$totalAfter) {
    echo "<h4 style='margin-top:20px'>$title</h4>";
    echo "<table><thead><tr>";
    foreach($columns as $col) echo "<th>$col</th>";
    echo "</tr></thead><tbody>";

    $sectionBefore = 0; $sectionVat = 0; $sectionAfter = 0;

    foreach($rows as $r){
        echo "<tr>";
        foreach ($columns as $col) {
            switch($col) {
                case 'الاسم':
                case 'الأصل':
                    echo "<td>".htmlspecialchars($r['name'] ?? '-')."</td>";
                    break;
                case 'المورد':
                    echo "<td>".htmlspecialchars($r['supplier_name'] ?? '-')."</td>";
                    break;
                case 'الكمية':
                    echo "<td>".htmlspecialchars($r['quantity'] ?? '-')."</td>";
                    break;
                case 'النوع':
                    echo "<td>".htmlspecialchars($r['type'] ?? '-')."</td>";
                    break;
                case 'الإجمالي قبل الضريبة':
                    echo "<td>".number_format($r['before'] ?? 0,3)."</td>";
                    break;
                case 'الضريبة':
                    echo "<td>".number_format($r['vat'] ?? 0,3)."</td>";
                    break;
                case 'الإجمالي بعد':
                    echo "<td>".number_format($r['after'] ?? 0,3)."</td>";
                    break;
                default:
                    echo "<td>-</td>";
                    break;
            }
        }
        echo "</tr>";

        $sectionBefore += $r['before'] ?? 0;
        $sectionVat    += $r['vat'] ?? 0;
        $sectionAfter  += $r['after'] ?? 0;
    }

    echo "<tr style='font-weight:bold;background:#f1f1f1'>
            <td colspan='".(count($columns)-3)."'>الإجمالي الفرعي</td>
            <td>".number_format($sectionBefore,3)."</td>
            <td>".number_format($sectionVat,3)."</td>
            <td>".number_format($sectionAfter,3)."</td>
          </tr></tbody></table>";

    $totalBefore += $sectionBefore;
    $totalVat    += $sectionVat;
    $totalAfter  += $sectionAfter;
}

$stmt = $pdo->prepare("
    SELECT 
        p.name,
        op.supplier_name,
        (CASE WHEN unit_vat > 0 THEN (p.price * p.quantity) ELSE (p.price * p.quantity * 1.15) END) AS `before`, 
        (CASE WHEN unit_vat > 0 THEN (p.price * p.quantity * 0.15) ELSE 0 END) AS `vat`, 
        (CASE WHEN unit_vat > 0 THEN (p.price * p.quantity * 1.15) ELSE (p.price * p.quantity * 1.15) END) AS `after` 
    FROM purchases p
    LEFT JOIN orders_purchases op ON p.order_id = op.id
    WHERE 1=1 $dateFilterPurchases
");
$stmt->execute($paramsPurchases);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN sub_expense = 'أخرى' OR sub_expense IS NULL OR sub_expense = '' 
            THEN CONCAT(main_expense, ' - ', expense_desc)
            ELSE CONCAT(main_expense, ' - ', sub_expense)
        END AS name, 
        (CASE WHEN has_vat=1 THEN expense_amount ELSE expense_amount * 1.15 END) AS `before`, 
        (CASE WHEN has_vat=1 THEN expense_amount * 0.15 ELSE 0 END) AS `vat`, 
        (CASE WHEN has_vat=1 THEN expense_amount * 1.15 ELSE expense_amount * 1.15 END) AS `after` 
    FROM expenses e
    WHERE 1=1 $dateFilterExpenses
");
$stmt->execute($paramsExpenses);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        a.name,
        a.quantity,
        a.type,
        (a.price * a.quantity) AS `before`,
        (CASE WHEN a.has_vat=1 THEN (a.price * a.quantity) ELSE a.price * a.quantity * 1.15 END) AS `before`
        (CASE WHEN a.has_vat=1 THEN a.price * a.quantity * 0.15 ELSE 0 END) AS `vat`,
        (CASE WHEN a.has_vat=1 THEN a.price * a.quantity * 1.15 ELSE a.price * a.quantity * 1.15 END) AS `after`
    FROM assets a
    WHERE 1=1 $dateFilterAssets
");
$stmt->execute($paramsAssets);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

renderSection("المشتريات", $purchases, ['الاسم','المورد','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
renderSection("المصروفات", $expenses, ['الاسم','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
renderSection("الأصول", $assets, ['الأصل','الكمية','النوع','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
?>

<table style="margin-top:30px;font-weight:bold;background:#d4edda">
  <tr>
    <td>الإجمالي الكلي قبل الضريبة</td>
    <td><?= number_format($totalBefore,3) ?></td>
  </tr>
  <tr>
    <td>إجمالي الضريبة</td>
    <td><?= number_format($totalVat,3) ?></td>
  </tr>
  <tr>
    <td>الإجمالي الكلي بعد الضريبة</td>
    <td><?= number_format($totalAfter,3) ?></td>
  </tr>
</table>

<script>
  window.print();
  window.onafterprint = function(){ window.history.back(); }
</script>
</body>
</html>
