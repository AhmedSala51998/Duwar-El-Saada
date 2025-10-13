<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    if ($oldData) {
        $orderId = $oldData['order_id'] ?? null;

        // استرجاع العهدة إذا كانت مدفوعة من العهدة
        if ($oldData['payment_source'] === 'عهدة') {
            // جلب كل المعاملات المرتبطة بالعملية
            $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type=? AND type_id=?");
            $stmtTx->execute(['purchase', $oldData['id']]); // استبدل 'purchase' بالنوع المناسب إذا كان خصم آخر
            $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

            foreach ($transactions as $tx) {
                // جلب العهدة الأصلية
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
                $stmtC->execute([$tx['custody_id']]);
                $custody = $stmtC->fetch();

                if ($custody) {
                    $newAmount = $custody['amount'] + $tx['amount'];
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                }
            }

            // حذف المعاملات بعد الإرجاع
            $pdo->prepare("DELETE FROM custody_transactions WHERE type=? AND type_id=?")->execute(['purchase', $oldData['id']]);
        }

        // حذف المنتج نفسه
        $pdo->prepare("DELETE FROM purchases WHERE id=?")->execute([$id]);

        // ✅ إعادة التعامل مع الفاتورة المرتبطة
        if ($orderId) {
            // جلب باقي المنتجات المرتبطة بنفس الفاتورة
            $stmtItems = $pdo->prepare("SELECT quantity, price FROM purchases WHERE order_id=?");
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
                    $total += $item['quantity'] * $item['price'];
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
    } else {
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'العملية غير موجودة'];
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
