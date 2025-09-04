<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $id = (int)$_POST['id'];
    $old = $pdo->prepare("SELECT invoice_image FROM gov_fees WHERE id=?"); $old->execute([$id]); $old_image = $old->fetchColumn();
    $img = upload_image('invoice_image') ?: $old_image;

    $stmt = $pdo->prepare("UPDATE gov_fees SET fee_title=?, fee_type=?, fee_amount=?, payer=?, invoice_image=? WHERE id=?");
    $stmt->execute([
        trim($_POST['fee_title']),
        trim($_POST['fee_type']),
        (float)($_POST['fee_amount'] ?? 0),
        $_POST['payer'],
        $img,
        $id
    ]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: gov_fees.php');
exit;
