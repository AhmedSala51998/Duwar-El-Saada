<?php

function add_activity_log(
    PDO $pdo,
    string $action,
    string $module,
    ?int $record_id = null,
    ?string $description = null
){
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? null;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            username,
            action,
            module,
            record_id,
            description,
            ip_address
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $user_id,
        $username,
        $action,
        $module,
        $record_id,
        $description,
        $ip
    ]);
}