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
        $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
        $stmtC->execute([$oldData['payer_name']]);
        $custody = $stmtC->fetch();
        if($custody){
            $newAmount = $custody['amount'] + $oldData['price'];
            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
        }
    }

    $pdo->prepare("DELETE FROM assets WHERE id=?")->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم الحذف بنجاح'];
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
