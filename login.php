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
/* ===== Right Section Redesigned ===== */

.login-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
}

.login-card-advanced {
    width: 100%;
    max-width: 440px;
    background: rgba(255,255,255,0.65);
    backdrop-filter: blur(12px);
    border-radius: 25px;
    padding: 40px 40px 35px;
    box-shadow: 
        0 8px 25px rgba(0,0,0,0.08),
        0 3px 8px rgba(0,0,0,0.05);
    animation: slideUp 0.8s ease forwards;
    opacity: 0;
}

@keyframes slideUp {
    0% { transform: translateY(25px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}

.logo-circle {
    width: 105px;
    height: 105px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ffb974, #ff8b2b);
    box-shadow: 0 8px 20px rgba(255,144,44,0.25);
    margin: auto;
    padding: 14px;
}

.title {
    font-weight: 700;
    color: #444;
}

.subtitle {
    font-size: 14px;
    color: #777;
}

/* Inputs */
.input-group-custom label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #555;
    font-size: 14px;
}

.input-box {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 14px;
    padding: 10px 14px;
    gap: 10px;
    border: 1px solid #eee;
    transition: 0.3s;
}

.input-box i {
    font-size: 18px;
    color: #ff7a00;
}

.input-box input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 16px;
}

.input-box:hover,
.input-box:focus-within {
    border-color: #ff7a00;
    box-shadow: 0 3px 10px rgba(255,126,0,0.15);
}

/* Button */
.btn-login {
    width: 100%;
    padding: 12px;
    border: none;
    background: linear-gradient(135deg, #ff8a00, #ff6b00);
    color: #fff;
    border-radius: 16px;
    font-size: 18px;
    font-weight: 600;
    box-shadow: 0 6px 18px rgba(255,102,0,0.25);
    transition: 0.25s;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255,102,0,0.32);
}

/* Bottom note */
.bottom-note {
    color: #777;
    font-size: 13px;
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

        <div class="login-wrapper">

            <div class="login-card-advanced">

                <div class="login-header text-center mb-4">
                    <div class="logo-circle mb-3">
                        <img src="assets/logo.png" width="85">
                    </div>
                    <h3 class="title">دوار السعادة</h3>
                    <p class="subtitle">تسجيل الدخول الى النظام</p>
                </div>

                <?php if($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
                <?php endif; ?>

                <form id="loginForm" method="post" class="mt-3">

                    <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

                    <div class="input-group-custom mb-3">
                        <label>اسم المستخدم</label>
                        <div class="input-box">
                            <i class="bi bi-person"></i>
                            <input name="username" id="username">
                        </div>
                    </div>

                    <div class="input-group-custom mb-4">
                        <label>كلمة المرور</label>
                        <div class="input-box">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" id="password">
                        </div>
                    </div>

                    <button class="btn-login">تسجيل الدخول</button>

                </form>

                <div class="bottom-note text-center mt-3">
                    دوار السعادة © 2025
                </div>

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
