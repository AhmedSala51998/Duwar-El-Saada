<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
if($id){
    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM assets WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    // استرجاع العهدة إذا كانت مدفوعة من العهدة
    if($oldData['payment_source'] === 'عهدة'){
        $amountToReturn = $oldData['price'] * $oldData['quantity'];

        // جلب كل العهد للشخص، الأقدم أولاً
        $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
        $stmtC->execute([$oldData['payer_name']]);
        $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        foreach($custodies as $custody){
            if($amountToReturn <= 0) break;

            // المبلغ الذي تم خصمه فعلياً من هذه العهدة
            $deductedBefore = ($custody['amount'] < $oldData['price'] * $oldData['quantity']) ? $custody['amount'] : $amountToReturn;

            $newAmount = $custody['amount'] + $deductedBefore;
            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

            // سجل العملية في custody_transactions كإرجاع
            $stmtTx = $pdo->prepare("
                INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmtTx->execute(['refund_asset', $oldData['id'], $custody['id'], $deductedBefore]);

            $amountToReturn -= $deductedBefore;
        }
    }


    $pdo->prepare("DELETE FROM assets WHERE id=?")->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم الحذف بنجاح'];
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
