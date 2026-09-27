<?php
require __DIR__ . '/config/config.php';
require_auth();
require_permission('dhimam.view');
require_once __DIR__ . '/libs/SimpleXLSXGen.php';

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
$note = $dateType === 'today' ? 'تقرير اليوم (' . $fromDate . ')' : ($dateType === 'yesterday' ? 'تقرير أمس (' . $fromDate . ')' : (($fromDate || $toDate) ? 'الفترة من ' . ($fromDate ?: 'البداية') . ' إلى ' . ($toDate ?: 'اليوم') : 'كل الذمم المالية'));
$data = [['تقرير الذمم المالية'], [$note]];
if ($kw !== '') $data[] = ['البحث: ' . $kw];
$data[] = [];
$data[] = ['#', 'اسم الشخص', 'المبلغ المستلم', 'البيان / الوصف', 'تاريخ الاستلام'];
$total = 0;
foreach ($rows as $row) {
    $amount = (float)$row['amount'];
    $total += $amount;
    $data[] = [(int)$row['id'], $row['person_name'], $amount, $row['description'], $row['received_at']];
}
$data[] = ['الإجمالي (' . count($rows) . ')', '', $total, '', ''];
Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('dhimam.xlsx');
