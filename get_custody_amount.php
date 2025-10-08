<?php
require __DIR__.'/config/config.php';
header('Content-Type: application/json');

$person = $_GET['person_name'] ?? '';

if ($person) {
    // جمع كل العُهد للشخص
    $stmt = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM custodies WHERE person_name=?");
    $stmt->execute([$person]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['amount' => $row['total_amount'] ?? 0]);
} else {
    echo json_encode(['amount' => 0]);
}
