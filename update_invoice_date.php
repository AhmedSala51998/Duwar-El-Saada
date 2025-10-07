<?php
require __DIR__ . '/config/config.php'; // أو ملف الاتصال بقاعدة البيانات عندك

$orderId = (int)($_POST['order_id'] ?? 0);
$date = $_POST['date'] ?? '';

if ($orderId && $date) {
    $stmt = $pdo->prepare("UPDATE orders_purchases SET created_at=? WHERE id=?");
    $stmt->execute([$date, $orderId]);
    echo "تم تحديث التاريخ بنجاح";
} else {
    echo "بيانات غير صحيحة";
}
