<?php
require __DIR__.'/config/config.php';
if(is_auth()){ header('Location: '.BASE_URL.'/home'); exit; }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!csrf_validate($_POST['_csrf'] ?? '')){ $error='طلب غير صالح.'; }
  else{
    $u = trim($_POST['username']??''); 
    $p = (string)($_POST['password']??'');
    $s = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1"); 
    $s->execute([$u]); 
    $user=$s->fetch();
    if($user && password_verify($p,$user['password_hash'])){
      $_SESSION['user_id']=$user['id']; 
      $_SESSION['username']=$user['username']; 
      $_SESSION['user_id_seq']=$user['user_id_seq'];
      header('Location: '.BASE_URL.'/home'); exit;
    } 
    else $error='بيانات الدخول غير صحيحة.';
  }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc(APP_NAME) ?> - دخول</title>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

<!-- Lottie Animation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>

<style>
body {
    font-family: 'Cairo', sans-serif;
    background: linear-gradient(135deg, #ffe2c8, #fff7f0);
    min-height: 100vh;
    overflow: hidden;
}

.page-wrapper {
    display: flex;
    height: 100vh;
}

.left-side {
    flex: 1;
    background: #fff3e6;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

#lottieBox {
    width: 450px;
    height: 450px;
}

.right-side {
    flex: 1;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
}

.login-card {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}

.btn-orange {
    background: #ff7b00;
    color: #fff;
    font-size: 18px;
    padding: 10px;
    border-radius: 12px;
}

.btn-orange:hover {
    background: #e56e00;
}
</style>

</head>
<body>

<div class="page-wrapper">

    <!-- left animation -->
    <div class="left-side d-flex align-items-center justify-content-center">
        
        <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.5/dist/dotlottie-wc.js" type="module"></script>

        <dotlottie-wc 
            src="https://lottie.host/10ef2e3b-cd9e-43d0-95f5-57641ab612cf/7MHGlQgtsb.lottie" 
            style="width: 620px;height: 620px" 
            autoplay 
            loop>
        </dotlottie-wc>

    </div>

    <!-- right form -->
    <div class="right-side">

        <div class="login-card">

            <div class="text-center mb-4">
                <img src="assets/logo.png" width="90">
                <h3 class="mt-2 fw-bold">دوار السعادة</h3>
                <p class="text-muted">تسجيل الدخول</p>
            </div>

            <?php if($error): ?>
            <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form id="loginForm" method="post">

                <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

                <div class="mb-3">
                    <label class="form-label">اسم المستخدم</label>
                    <input name="username" class="form-control" id="username">
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <button class="btn btn-orange w-100">دخول</button>

            </form>

            <div class="text-center mt-3 small text-muted">
                دوار السعادة 2025
            </div>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
// === معالجة الأخطاء بالـ jQuery قبل الإرسال ====
$("#loginForm").on("submit", function(e){
    let u = $("#username").val().trim();
    let p = $("#password").val().trim();

    $(".is-invalid").removeClass("is-invalid");

    if(u.length < 3){
        $("#username").addClass("is-invalid");
        e.preventDefault();
        return;
    }

    if(p.length < 3){
        $("#password").addClass("is-invalid");
        e.preventDefault();
        return;
    }
});
</script>


</body>
</html>
