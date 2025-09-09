<?php
require __DIR__.'/config/config.php';
header('Content-Type: application/json');

$person = $_GET['person_name'] ?? '';

if($person){
    $stmt = $pdo->prepare("SELECT amount FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
    $stmt->execute([$person]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['amount' => $row['amount'] ?? 0]);
} else {
    echo json_encode(['amount' => 0]);
}
