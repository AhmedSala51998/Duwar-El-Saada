<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    // بدء المعاملة
    $pdo->beginTransaction();
    try {
        // جلب البيانات القديمة
        $old = $pdo->prepare("SELECT * FROM assets WHERE id=?");
        $old->execute([$id]);
        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        if ($oldData) {
            // استرجاع العهدة إذا كانت مدفوعة من العهدة
            if ($oldData['payment_source'] === 'عهدة') {
                // جلب كل المعاملات المرتبطة
                $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type=? AND type_id=?");
                $stmtTx->execute(['asset', $oldData['id']]);
                $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

                foreach ($transactions as $tx) {
                    // جلب العهدة الأصلية
                    $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
                    $stmtC->execute([$tx['custody_id']]);
                    $custody = $stmtC->fetch();

                    if ($custody) {
                        // إضافة المبلغ المسترد إلى العهدة الأصلية
                        $newAmount = $custody['amount'] + $tx['amount'];
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                    }
                }

                // حذف المعاملات بعد الإرجاع
                $pdo->prepare("DELETE FROM custody_transactions WHERE type=? AND type_id=?")->execute(['asset', $oldData['id']]);
            }

            // حذف الأصل نفسه
            $pdo->prepare("DELETE FROM assets WHERE id=?")->execute([$id]);
        }

        // إنهاء المعاملة بنجاح
        $pdo->commit();
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم الحذف بنجاح'];
    } catch (Exception $e) {
        // إذا حصل أي خطأ، يتم التراجع عن كل التغييرات
        $pdo->rollBack();
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
