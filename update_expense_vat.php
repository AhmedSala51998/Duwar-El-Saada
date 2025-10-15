<?php
require __DIR__ . '/config/config.php'; // أو ملف الاتصال بقاعدة البيانات عندك

// استقبال البيانات من AJAX
$id = (int)($_POST['id'] ?? 0);
$vat_value = (float)($_POST['vat_value'] ?? 0);
$total_amount = (float)($_POST['total_amount'] ?? 0);
$has_vat = (int)($_POST['has_vat'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo "Invalid expense ID";
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE expenses 
        SET 
            has_vat = ?, 
            vat_value = ?

        WHERE id = ?
    ");
    $stmt->execute([$has_vat, $vat_value, $id]);
    echo "تم تحديث الضريبة والإجمالي بنجاح ✅";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>
