<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
if($id > 0){
    $pdo->prepare("DELETE FROM expenses WHERE id=?")->execute([$id]);
    $_SESSION['toast'] = ['type'=>'success','msg'=>'تم الحذف بنجاح'];
}

header('Location: '.BASE_URL.'/expenses.php');
exit;
