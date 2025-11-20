<?php
require __DIR__.'/config/config.php';
if(is_auth()){ header('Location: '.BASE_URL.'/home'); exit; }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!csrf_validate($_POST['_csrf'] ?? '')){ $error='طلب غير صالح.'; }
  else{
    $u = trim($_POST['username']??''); $p = (string)($_POST['password']??'');
    $s = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1"); $s->execute([$u]); $user=$s->fetch();
    if($user && password_verify($p,$user['password_hash'])){
      $_SESSION['user_id']=$user['id']; $_SESSION['username']=$user['username'];
      header('Location: '.BASE_URL.'/home'); exit;
    } else $error='بيانات الدخول غير صحيحة.';
  }
}
?><!doctype html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc(APP_NAME) ?> - دخول</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="assets/css/theme.css" rel="stylesheet"></head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:#fff7f0">
<div class="card p-4 shadow" style="min-width:360px">
  <div class="text-center mb-3">
    <img src="assets/logo.png" width="100" class="mb-2"><h3 class="mb-0">دوار السعادة</h3><div class="text-muted small">تسجيل الدخول</div>
  </div>
  <?php if($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
  <form method="post" class="vstack gap-3">
    <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
    <div><label class="form-label">اسم المستخدم</label><input name="username" class="form-control" required></div>
    <div><label class="form-label">كلمة المرور</label><input type="password" name="password" class="form-control" required></div>
    <button class="btn btn-orange w-100">دخول</button>
  </form>
  <div class="text-center mt-3 small text-muted">دوار السعادة 2025</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
