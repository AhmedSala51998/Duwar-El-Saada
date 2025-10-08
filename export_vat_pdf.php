<?php
require __DIR__.'/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        foreach ($columns as $col) {
            switch($col) {
                case 'الاسم':
                case 'الأصل':  // دعم الأصول
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
                    echo "<td>".number_format($r['before'] ?? 0,2)."</td>";
                    break;
                case 'الضريبة':
                    echo "<td>".number_format($r['vat'] ?? 0,2)."</td>";
                    break;
                case 'الإجمالي بعد':
                    echo "<td>".number_format($r['after'] ?? 0,2)."</td>";
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
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        op.supplier_name,
        (p.price * p.quantity) AS `before`,
        (p.price * p.quantity * 0.15) AS `vat`,
        (p.price * p.quantity * 1.15) AS `after`
    FROM purchases p
    LEFT JOIN orders_purchases op ON p.order_id = op.id
    WHERE 1=1 $dateFilter
");
$stmt->execute($params);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- المصروفات ----------------------------
// ---------------------------- المصروفات ----------------------------
$stmt = $pdo->prepare("SELECT 
    CASE 
        WHEN sub_expense = 'أخرى' OR sub_expense IS NULL OR sub_expense = '' 
        THEN CONCAT(main_expense, ' - ', expense_desc)
        ELSE CONCAT(main_expense, ' - ', sub_expense)
    END AS name, 
    expense_amount AS `before`, 
    (CASE WHEN has_vat=1 THEN expense_amount * 0.15 ELSE 0 END) AS `vat`, 
    (CASE WHEN has_vat=1 THEN expense_amount * 1.15 ELSE expense_amount END) AS `after` 
FROM expenses 
WHERE 1=1 $dateFilter
");
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------- الأصول ----------------------------
$stmt = $pdo->prepare("
    SELECT 
        a.name,
        a.quantity,
        a.type,
        (a.price * a.quantity) AS `before`,
        (CASE WHEN a.has_vat=1 THEN a.price * a.quantity * 0.15 ELSE 0 END) AS `vat`,
        (CASE WHEN a.has_vat=1 THEN a.price * a.quantity * 1.15 ELSE a.price * a.quantity END) AS `after`
    FROM assets a
    WHERE 1=1 $dateFilter
");
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
// عرض الفترة الزمنية
if ($from_date || $to_date) {
    $fromText = $from_date ? date('Y-m-d', strtotime($from_date)) : 'بداية';
    $toText   = $to_date   ? date('Y-m-d', strtotime($to_date))   : 'اليوم';
    echo "<p style='text-align:center;font-weight:bold'>الفترة من $fromText إلى $toText</p>";
} else {
    echo "<p style='text-align:center;font-weight:bold'>كل التقرير</p>";
}

// المشتريات: ['الاسم','المورد','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد']
renderSection("المشتريات", $purchases, ['الاسم','المورد','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);

// المصروفات تبقى زي ما هي
renderSection("المصروفات", $expenses, ['الاسم','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);

// الأصول: ['الأصل','الكمية','النوع','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد']
renderSection("الأصول", $assets, ['الأصل','الكمية','النوع','الإجمالي قبل الضريبة','الضريبة','الإجمالي بعد'], $totalBefore,$totalVat,$totalAfter);
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
