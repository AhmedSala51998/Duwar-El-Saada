<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
if($id){
    $pdo->prepare("DELETE FROM rentals WHERE id=?")->execute([$id]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تم الحذف بنجاح'];
}
header('Location: rentals.php');
exit;
