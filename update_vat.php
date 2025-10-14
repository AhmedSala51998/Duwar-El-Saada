<?php
require __DIR__.'/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $vat = floatval($_POST['vat'] ?? 0);
    $allTotal = floatval($_POST['all_total'] ?? 0);
    $vatRate = floatval($_POST['vat_rate'] ?? 0);

    if ($orderId > 0) {
        if ($vatRate === 0) {
            // ✅ لو الضريبة صفر
            $stmt = $pdo->prepare("
                UPDATE orders_purchases 
                SET vat = 0, all_total = (
                    SELECT SUM(unit_total) FROM purchases WHERE order_id = ?
                )
                WHERE id = ?
            ");
            $stmt->execute([$orderId, $orderId]);

            $stmt2 = $pdo->prepare("
                UPDATE purchases 
                SET unit_vat = 0, unit_all_total = unit_total 
                WHERE order_id = ?
            ");
            $stmt2->execute([$orderId]);

        } else {
            // ✅ لو النسبة 15% (أو أي رقم آخر)
            // أولاً نحسب لكل صنف الضريبة والإجمالي بعد الضريبة
            $purchases = $pdo->prepare("SELECT id, unit_total FROM purchases WHERE order_id = ?");
            $purchases->execute([$orderId]);
            $rows = $purchases->fetchAll();

            foreach ($rows as $r) {
                $unitVat = $r['unit_total'] * $vatRate;
                $unitAllTotal = $r['unit_total'] + $unitVat;

                $upd = $pdo->prepare("
                    UPDATE purchases 
                    SET unit_vat = ?, unit_all_total = ? 
                    WHERE id = ?
                ");
                $upd->execute([$unitVat, $unitAllTotal, $r['id']]);
            }

            // بعد ما نحسب الضريبة لكل الأصناف، نحدث الفاتورة الرئيسية
            $stmt = $pdo->prepare("
                UPDATE orders_purchases 
                SET vat = ?, all_total = ? 
                WHERE id = ?
            ");
            $stmt->execute([$vat, $allTotal, $orderId]);
        }

        echo "ok";
    } else {
        echo "Invalid order ID";
    }
}
?>