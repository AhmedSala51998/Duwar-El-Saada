<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

$id=(int)($_GET['id']??0);
if($id){
    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
    $stmt->execute([$id]); $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if($purchase['payment_source']==='custody'){
        $amount = $purchase['quantity']*$purchase['price'];
        $pdo->prepare("UPDATE custodies SET amount = amount + ? WHERE person_name=?")->execute([$amount,$purchase['payer_name']]);
    }

    $pdo->prepare("DELETE FROM purchases WHERE id=?")->execute([$id]);
    session_start(); $_SESSION['toast']=['type'=>'success','msg'=>'تم الحذف بنجاح'];
}
header('Location: purchases.php'); exit;
