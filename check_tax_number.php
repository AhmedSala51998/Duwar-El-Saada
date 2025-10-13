<?php
require __DIR__ . '/config/config.php'; // الاتصال بقاعدة البيانات

header('Content-Type: application/json; charset=utf-8');

$tax = trim($_GET['tax'] ?? '');

if ($tax === '') {
    echo json_encode(['error' => 'Missing tax number']);
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE tax_number = ?");
$stmt->execute([$tax]);
$exists = $stmt->fetchColumn() > 0;

echo json_encode(['exists' => $exists]);
