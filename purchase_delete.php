<?php require __DIR__.'/config/config.php'; require_role(['admin','manager']);
$id=(int)($_GET['id']??0); if($id){ $pdo->prepare("DELETE FROM purchases WHERE id=?")->execute([$id]); session_start();
$_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم الحذف بنجاح'];  }
header('Location: '.BASE_URL.'/purchases.php');
