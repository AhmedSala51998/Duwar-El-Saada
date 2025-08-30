<?php require __DIR__.'/config/config.php'; require_role('admin');
if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
  $u=trim($_POST['username']); $p=(string)$_POST['password']; $r=$_POST['role']??'staff';
  $pdo->prepare("INSERT INTO users(username,password_hash,role) VALUES(?,?,?)")->execute([$u,password_hash($p,PASSWORD_DEFAULT),$r]);
  flash('msg','تم إنشاء المستخدم');
}
header('Location: '.BASE_URL.'/users.php');