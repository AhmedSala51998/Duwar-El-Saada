<?php
require __DIR__ . '/config/config.php'; // تأكد أن هذا الملف يحتوي على الاتصال بقاعدة البيانات $pdo

// تأكيد أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

$id = (int)($_POST['id'] ?? 0);
$vat_value = (float)($_POST['vat_value'] ?? 0);
$total_amount = (float)($_POST['total_amount'] ?? 0);
$has_vat = (int)($_POST['has_vat'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Missing or invalid ID.');
}

try {
    $stmt = $pdo->prepare("UPDATE assets 
        SET vat_value = ?, total_amount = ?, has_vat = ? 
        WHERE id = ?");
    $stmt->execute([$vat_value, $total_amount, $has_vat, $id]);

    echo "تم تحديث بيانات الضريبة بنجاح ✅";
} catch (Exception $e) {
    http_response_code(500);
    echo "حدث خطأ أثناء التحديث: " . $e->getMessage();
}
