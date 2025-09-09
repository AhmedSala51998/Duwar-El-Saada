<?php
require __DIR__.'/config/config.php';
$payer = $_GET['payer'] ?? '';
$stmt = $pdo->prepare("SELECT amount FROM custodies WHERE person_name=?");
$stmt->execute([$payer]);
$amount = (float)($stmt->fetchColumn() ?? 0);
echo json_encode(['advance_amount' => $amount]);
