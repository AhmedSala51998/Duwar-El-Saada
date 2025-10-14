<?php
require __DIR__ . '/config/config.php'; // أو أي ملف فيه الاتصال بـ $pdo

header('Content-Type: application/json; charset=utf-8');

$bill = $_GET['bill'] ?? '';
$supplier = $_GET['supplier'] ?? '';

if (!$bill || !$supplier) {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE bill_number = ? AND supplier_name = ?");
$stmt->execute([$bill, $supplier]);
$exists = $stmt->fetchColumn() > 0;

echo json_encode(['exists' => $exists]);
