<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
if($id){
    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    // استرجاع العهدة إذا كانت مدفوعة من العهدة
    if($oldData['payment_source'] === 'عهدة'){
        $amountToRefund = $oldData['expense_amount'];

        // جلب كل العهد المتاحة للشخص، الأقدم أولاً
        $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
        $stmtC->execute([$oldData['payer_name']]);
        $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        foreach($custodies as $custody){
            if($amountToRefund <= 0) break;

            // كم نقدر نضيف للعهدة الحالية
            $add = $amountToRefund; // لأن استرجاع، نضيف كامل المبلغ المتبقي
            $newAmount = $custody['amount'] + $add;

            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            $amountToRefund -= $add; // الآن الصفر غالباً بعد أول تحديث
        }
    }

    $pdo->prepare("DELETE FROM expenses WHERE id=?")->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم الحذف بنجاح'];
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
