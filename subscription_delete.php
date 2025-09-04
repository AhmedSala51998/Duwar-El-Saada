<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id=(int)($_GET['id']??0);
if($id){
    $pdo->prepare("DELETE FROM subscriptions WHERE id=?")->execute([$id]);
    $_SESSION['toast']=['type'=>'success','msg'=>'تم الحذف بنجاح'];
}
header('Location: subscriptions.php');
exit;
