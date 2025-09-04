<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $stmt = $pdo->prepare("INSERT INTO gov_fees(fee_title,fee_type,fee_amount,payer,invoice_image) VALUES(?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['fee_title']),
        trim($_POST['fee_type']),
        (float)($_POST['fee_amount'] ?? 0),
        $_POST['payer'],
        upload_image('invoice_image')
    ]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: gov_fees.php');
exit;
