<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $id = (int)$_POST['id'];
    $old = $pdo->prepare("SELECT invoice_image FROM rentals WHERE id=?");
    $old->execute([$id]);
    $old_image = $old->fetchColumn();

    $img = upload_image('invoice_image') ?: $old_image;

    $stmt = $pdo->prepare("UPDATE rentals SET rental_name=?, payment_type=?, rental_price=?, rental_kind=?, payer=?, invoice_image=? WHERE id=?");
    $stmt->execute([
        trim($_POST['rental_name']),
        $_POST['payment_type'],
        (float)($_POST['rental_price'] ?? 0),
        trim($_POST['rental_kind'] ?? ''),
        $_POST['payer'],
        $img,
        $id
    ]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: rentals.php');
exit;
