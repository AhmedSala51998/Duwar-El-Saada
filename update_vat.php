<?php
require __DIR__.'/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $vat = floatval($_POST['vat'] ?? 0);
    $allTotal = floatval($_POST['all_total'] ?? 0);
    $vatRate = floatval($_POST['vat_rate'] ?? 0);

    if ($orderId > 0) {

        // تحديث جدول orders_purchases
        $stmt = $pdo->prepare("UPDATE orders_purchases SET vat = ?, all_total = ? WHERE id = ?");
        $stmt->execute([$vat, $allTotal, $orderId]);

        // لو الضريبة 0 صفر كل القيم في purchases المرتبطة
        if ($vatRate === 0) {
            $stmt2 = $pdo->prepare("UPDATE purchases 
                SET unit_vat = 0, unit_all_total = unit_total 
                WHERE order_id = ?");
            $stmt2->execute([$orderId]);
        }

        echo "VAT and totals updated successfully";
    } else {
        echo "Invalid order ID";
    }
}
?>
