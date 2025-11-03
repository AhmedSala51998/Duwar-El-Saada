<?php require __DIR__.'/config/config.php'; require_permission('users.delete');
$id=(int)($_GET['id']??0);
if($id && $id!=($_SESSION['user_id']??0)){ $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]); $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تم حذف المستخدم'
        ]; }
header('Location: '.BASE_URL.'/users.php');