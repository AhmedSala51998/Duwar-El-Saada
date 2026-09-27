<?php
require __DIR__ . '/config/config.php';
require_auth();
require_permission('dhimam.view');

$kw = trim($_GET['kw'] ?? '');
$dateType = $_GET['date_type'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
if ($dateType === 'today' || $dateType === 'yesterday') {
    $fromDate = $toDate = date('Y-m-d', $dateType === 'yesterday' ? strtotime('-1 day') : time());
}
foreach ([$fromDate, $toDate] as $date) {
    if ($date !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int)substr($date, 5, 2), (int)substr($date, 8, 2), (int)substr($date, 0, 4)))) {
        http_response_code(400);
        exit('تاريخ غير صالح');
    }
}
$where = ['1=1'];
$params = [];
if ($kw !== '') {
    $where[] = '(person_name LIKE ? OR description LIKE ?)';
    $params[] = "%$kw%";
    $params[] = "%$kw%";
}
if ($fromDate !== '') {
    $where[] = 'received_at >= ?';
    $params[] = $fromDate;
}
if ($toDate !== '') {
    $where[] = 'received_at <= ?';
    $params[] = $toDate;
}
$stmt = $pdo->prepare('SELECT id, person_name, amount, description, received_at FROM dhimam WHERE ' . implode(' AND ', $where) . ' ORDER BY received_at DESC, id DESC');
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = array_sum(array_map(static fn($row) => (float)$row['amount'], $rows));
$note = $dateType === 'today' ? 'تقرير اليوم (' . $fromDate . ')' : ($dateType === 'yesterday' ? 'تقرير أمس (' . $fromDate . ')' : (($fromDate || $toDate) ? 'الفترة من ' . ($fromDate ?: 'البداية') . ' إلى ' . ($toDate ?: 'اليوم') : 'كل الذمم المالية'));
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>تقرير الذمم المالية</title>
<style>
body { font-family: Cairo, Arial, sans-serif; margin: 24px; }
.logo { float: left; width: 60px; }
h2, .note { text-align: center; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th, td { border: 1px solid #ddd; padding: 7px; text-align: center; overflow-wrap: anywhere; }
th, tfoot { background: #f1f1f1; }
.description { white-space: pre-line; }
.actions { display: flex; justify-content: center; gap: 15px; margin: 20px; }
button { padding: 10px 20px; border: 0; border-radius: 8px; color: #fff; cursor: pointer; font-size: 16px; }
@media print { @page { size: A4 landscape; margin: 12mm; } body { margin: 0; font-size: 10px; } .actions { display: none; } thead { display: table-header-group; } tr { break-inside: avoid; } th, td { padding: 4px; } }
</style>
</head>
<body>
<img class="logo" src="<?= esc(getSystemSettings('secondary_logo') ?: '/assets/logo.png') ?>" alt="شعار الشركة">
<h2>تقرير الذمم المالية</h2>
<p class="note"><?= esc($note) ?><?= $kw !== '' ? ' — البحث: ' . esc($kw) : '' ?></p>
<div class="actions"><button style="background:#4caf50" onclick="window.print()">طباعة التقرير / حفظ PDF</button><button style="background:#f44336" onclick="history.back()">العودة للصفحة السابقة</button></div>
<table>
<thead><tr><th>#</th><th>اسم الشخص</th><th>المبلغ المستلم</th><th>البيان / الوصف</th><th>تاريخ الاستلام</th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr><td><?= (int)$row['id'] ?></td><td><?= esc($row['person_name']) ?></td><td><?= number_format((float)$row['amount'], 2) ?></td><td class="description"><?= esc($row['description']) ?></td><td><?= esc($row['received_at']) ?></td></tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">لا توجد ذمم مسجلة</td></tr><?php endif; ?>
</tbody>
<tfoot><tr><td colspan="2">الإجمالي (<?= count($rows) ?>)</td><td><?= number_format($total, 2) ?></td><td colspan="2"></td></tr></tfoot>
</table>
</body>
</html>
