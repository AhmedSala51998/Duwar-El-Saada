<?php
require __DIR__ . '/config/config.php'; // ملف الاتصال بقاعدة البيانات

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

$id = (int)($_POST['id'] ?? 0);
$date = trim($_POST['date'] ?? '');

if ($id <= 0 || empty($date)) {
    http_response_code(400);
    exit('Missing or invalid data.');
}

try {
    $stmt = $pdo->prepare("UPDATE custodies SET taken_at = ? WHERE id = ?");
    $stmt->execute([$date, $id]);

    echo "تم تحديث تاريخ العهدة بنجاح 🗓️";
} catch (Exception $e) {
    http_response_code(500);
    echo "حدث خطأ أثناء تحديث التاريخ: " . $e->getMessage();
}
