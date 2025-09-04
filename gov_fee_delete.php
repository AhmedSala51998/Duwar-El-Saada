<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
if($id){
    $pdo->prepare("DELETE FROM gov_fees WHERE id=?")->execute([$id]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تم الحذف بنجاح'];
}
header('Location: gov_fees.php');
exit;
