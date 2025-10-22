<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if($id){
    $pdo->beginTransaction(); // بدء المعاملة
    try {
        // جلب البيانات القديمة
        $old = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
        $old->execute([$id]);
        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        // استرجاع العهدة القديمة إذا كان مصدر الدفع "عهدة"
        if ($oldData['payment_source'] === 'عهدة') {
            // جلب كل المعاملات السابقة المرتبطة بهذا الصنف
            $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type='expense' AND type_id=?");
            $stmtTx->execute([$oldData['id']]);
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
            $pdo->prepare("DELETE FROM custody_transactions WHERE type='expense' AND type_id=?")->execute([$oldData['id']]);
        }

        // حذف المصروف نفسه
        $pdo->prepare("DELETE FROM expenses WHERE id=?")->execute([$id]);

        $pdo->commit(); // إنهاء المعاملة بنجاح
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم الحذف بنجاح'];

    } catch (Exception $e) {
        $pdo->rollBack(); // التراجع عن كل العمليات في حالة الخطأ
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
