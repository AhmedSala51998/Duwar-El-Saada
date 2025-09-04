<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $id=(int)$_POST['id'];
    $old=$pdo->prepare("SELECT invoice_image FROM subscriptions WHERE id=?"); $old->execute([$id]); $old_image=$old->fetchColumn();
    $img = upload_image('invoice_image') ?: $old_image;

    $stmt = $pdo->prepare("UPDATE subscriptions SET service_name=?, subscribers=?, subscription_type=?, service_price=?, payer=?, invoice_image=? WHERE id=?");
    $stmt->execute([
        trim($_POST['service_name']),
        trim($_POST['subscribers']),
        $_POST['subscription_type'],
        (float)($_POST['service_price'] ?? 0),
        $_POST['payer'],
        $img,
        $id
    ]);
    $_SESSION['toast']=['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: subscriptions.php');
exit;
