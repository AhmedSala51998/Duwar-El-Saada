<?php require __DIR__.'/config/config.php'; require_role('admin');
if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
  $id=(int)$_POST['id']; $u=trim($_POST['username']); $r=$_POST['role']??'staff'; $pwd=(string)($_POST['password']??'');
  if($pwd){ $pdo->prepare("UPDATE users SET username=?, role=?, password_hash=? WHERE id=?")->execute([$u,$r,password_hash($pwd,PASSWORD_DEFAULT),$id]); }
  else{ $pdo->prepare("UPDATE users SET username=?, role=? WHERE id=?")->execute([$u,$r,$id]); }
  flash('msg','تم تحديث المستخدم');
}
header('Location: '.BASE_URL.'/users.php');