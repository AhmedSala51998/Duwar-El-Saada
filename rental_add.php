<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $stmt = $pdo->prepare("INSERT INTO rentals(rental_name,payment_type,rental_price,rental_kind,payer,invoice_image) VALUES(?,?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['rental_name']),
        $_POST['payment_type'],
        (float)($_POST['rental_price'] ?? 0),
        trim($_POST['rental_kind'] ?? ''),
        $_POST['payer'],
        upload_image('invoice_image')
    ]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: rentals.php');
exit;
