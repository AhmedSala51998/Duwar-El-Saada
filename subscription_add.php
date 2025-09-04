<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $stmt = $pdo->prepare("INSERT INTO subscriptions(service_name,subscribers,subscription_type,service_price,payer,invoice_image) VALUES(?,?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['service_name']),
        trim($_POST['subscribers']),
        $_POST['subscription_type'],
        (float)($_POST['service_price'] ?? 0),
        $_POST['payer'],
        upload_image('invoice_image')
    ]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: subscriptions.php');
exit;
