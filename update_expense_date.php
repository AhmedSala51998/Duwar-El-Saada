<?php
require __DIR__ . '/config/config.php'; // نفس ملف الاتصال

$id = (int)($_POST['id'] ?? 0);
$date = trim($_POST['date'] ?? '');

if ($id <= 0 || empty($date)) {
    http_response_code(400);
    echo "Invalid data";
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE expenses SET created_at = ? WHERE id = ?");
    $stmt->execute([$date, $id]);
    echo "تم تحديث التاريخ بنجاح ✅";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>
