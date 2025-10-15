<?php
require __DIR__.'/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $vatRate = floatval($_POST['vat_rate'] ?? 0);

    if ($orderId > 0) {

        if ($vatRate === 0) {
            // ✅ لو الضريبة صفر
            $pdo->prepare("
                UPDATE orders_purchases 
                SET vat = 0
                WHERE id = ?
            ")->execute([$orderId, $orderId]);

            $pdo->prepare("
                UPDATE purchases 
                SET unit_vat = 0 
                WHERE order_id = ?
            ")->execute([$orderId]);

        } else {
            // ✅ لو النسبة فيها قيمة (مثلاً 15%)
            // نحسب لكل صنف الضريبة والإجمالي بعد الضريبة
            $purchases = $pdo->prepare("SELECT id, unit_total FROM purchases WHERE order_id = ?");
            $purchases->execute([$orderId]);
            $rows = $purchases->fetchAll();

            foreach ($rows as $r) {
                $unitVat = $r['unit_total'] * $vatRate;
                $unitAllTotal = $r['unit_total'] + $unitVat;

                $pdo->prepare("
                    UPDATE purchases 
                    SET unit_vat = ?
                    WHERE id = ?
                ")->execute([$unitVat, $unitAllTotal, $r['id']]);
            }

            // ✅ بعد التحديث، نحسب الإجماليات من قاعدة البيانات نفسها
            $totals = $pdo->prepare("
                SELECT 
                    SUM(unit_vat) AS total_vat, 
                    SUM(unit_all_total) AS total_all
                FROM purchases
                WHERE order_id = ?
            ");
            $totals->execute([$orderId]);
            $t = $totals->fetch();

            // ✅ نحدث الفاتورة الرئيسية بناءً على القيم الحقيقية
            $pdo->prepare("
                UPDATE orders_purchases 
                SET vat = ?
                WHERE id = ?
            ")->execute([$t['total_vat'], $orderId]);
        }

        echo "ok";
    } else {
        echo "Invalid order ID";
    }
}
?>
