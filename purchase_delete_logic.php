<?php
// هذا هو محتوى purchase_delete_logic.php مع استخدام Transaction
if ($oldData) {
    $pdo->beginTransaction(); // بدء المعاملة

    try {
        $orderId = $oldData['order_id'] ?? null;

        // استرجاع العهدة إذا كانت مدفوعة من العهدة
        if ($oldData['payment_source'] === 'عهدة') {
            $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type='purchase' AND type_id=?");
            $stmtTx->execute([$oldData['id']]);
            $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

            foreach ($transactions as $tx) {
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
                $stmtC->execute([$tx['custody_id']]);
                $custody = $stmtC->fetch();

                if ($custody) {
                    $newAmount = $custody['amount'] + $tx['amount'];
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                }
            }

            // حذف المعاملات بعد الإرجاع
            $pdo->prepare("DELETE FROM custody_transactions WHERE type='purchase' AND type_id=?")->execute([$oldData['id']]);
        }

        // حذف المنتج نفسه
        $pdo->prepare("DELETE FROM purchases WHERE id=?")->execute([$oldData['id']]);

        // ✅ إعادة التعامل مع الفاتورة المرتبطة
        if ($orderId) {
            // جلب باقي المنتجات المرتبطة بنفس الفاتورة
            $stmtItems = $pdo->prepare("SELECT total_packages, total_price FROM purchases WHERE order_id=?");
            $stmtItems->execute([$orderId]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            if (count($items) === 0) {
                // لو مفيش منتجات متبقية نحذف الفاتورة نفسها
                $pdo->prepare("DELETE FROM orders_purchases WHERE id=?")->execute([$orderId]);
                $_SESSION['toast'] = ['type'=>'success','msg'=>'تم حذف المنتج والفاتورة المرتبطة به بنجاح'];
            } else {
                // لو فيه منتجات تانية نعيد الحساب
                $total = 0;
                foreach ($items as $item) {
                    $total += $item['total_packages'] * $item['total_price'];
                }

                $vat = $total * 0.15;
                $allTotal = $total + $vat;

                $pdo->prepare("UPDATE orders_purchases SET total=?, vat=?, all_total=? WHERE id=?")
                    ->execute([$total, $vat, $allTotal, $orderId]);

                $_SESSION['toast'] = ['type'=>'success','msg'=>'تم حذف المنتج وتحديث إجماليات الفاتورة'];
            }
        } else {
            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم حذف العملية بنجاح'];
        }

        $pdo->commit(); // تأكيد المعاملة
    } catch (\Exception $e) {
        $pdo->rollBack(); // التراجع عن كل التغييرات في حالة الخطأ
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'حدث خطأ أثناء الحذف: ' . $e->getMessage()];
    }
} else {
    $_SESSION['toast'] = ['type'=>'warning','msg'=>'العملية غير موجودة'];
}
?>
