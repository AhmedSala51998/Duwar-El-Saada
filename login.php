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
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Cairo', sans-serif;
    background: linear-gradient(135deg, #ffe2c8, #fff7f0);
    min-height: 100vh;
    margin: 0;
}

/* Layout */
.page-wrapper {
    display: flex;
    height: 100vh;
}

.left-side {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff3e6;
    padding: 20px;
}

.right-side {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px;
}

/* Form Card */
.login-card-advanced {
    width: 100%;
    max-width: 430px;
    background: rgba(255,255,255,0.70);
    border-radius: 25px;
    padding: 40px;
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.10);
    animation: fadeUp 0.8s ease forwards;
    opacity: 0;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(25px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Logo */
.logo-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffa64d, #ff7b00);
    box-shadow: 0 10px 20px rgba(255,128,0,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
}

.logo-circle img {
    width: 80px;
}

/* Inputs */
.input-label {
    font-weight: 600;
    font-size: 14px;
}

.input-box {
    border: 1px solid #eee;
    background: #fff;
    display: flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 14px;
    transition: .3s;
    gap: 10px;
}

.input-box i {
    color: #ff7a00;
    font-size: 18px;
}

.input-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 15px;
}

.input-box:hover,
.input-box:focus-within {
    border-color: #ff8a00;
    box-shadow: 0 3px 10px rgba(255,126,0,0.18);
}

/* Button */
.btn-login {
    width: 100%;
    border: none;
    padding: 12px;
    background: linear-gradient(135deg, #ff8a00, #ff6a00);
    border-radius: 16px;
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    box-shadow: 0 5px 18px rgba(255,102,0,0.25);
    transition: .25s;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255,102,0,0.32);
}

/* Mobile Responsive */
@media(max-width: 992px) {
    .page-wrapper {
        flex-direction: column;
        height: auto;
    }
    .left-side {
        order: 1;
        padding: 30px 10px;
    }
    .right-side {
        order: 2;
        padding: 25px;
    }
    dotlottie-wc {
        width: 320px !important;
        height: 320px !important;
    }
}

@media(max-width: 576px) {
    .login-card-advanced {
        padding: 25px;
        border-radius: 18px;
    }
    .logo-circle {
        width: 90px;
        height: 90px;
    }
}
</style>

</head>
<body>

<div class="page-wrapper">

    <!-- LEFT -->
    <div class="left-side">
        <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.5/dist/dotlottie-wc.js" type="module"></script>

        <dotlottie-wc 
            src="https://lottie.host/10ef2e3b-cd9e-43d0-95f5-57641ab612cf/7MHGlQgtsb.lottie"
            style="width: 550px;height: 550px"
            autoplay 
            loop>
        </dotlottie-wc>
    </div>

    <!-- RIGHT -->
    <div class="right-side">

        <div class="login-card-advanced">

            <div class="text-center mb-4">
                <div class="logo-circle mb-3">
                    <img src="assets/logo.png">
                </div>
                <h3 class="fw-bold text-dark">دوار السعادة</h3>
                <p class="text-muted small">تسجيل الدخول للنظام</p>
            </div>

            <?php if($error): ?>
            <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form id="loginForm" method="post">

                <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

                <label class="input-label mb-1">اسم المستخدم</label>
                <div class="input-box mb-3">
                    <i class="fa fa-user"></i>
                    <input name="username" id="username">
                </div>

                <label class="input-label mb-1">كلمة المرور</label>
                <div class="input-box mb-4">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" id="password">
                </div>

                <button class="btn-login">تسجيل الدخول</button>

            </form>

            <p class="text-center mt-3 text-muted small">
                دوار السعادة © 2025
            </p>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$("#loginForm").on("submit", function(e){
    let u = $("#username").val().trim();
    let p = $("#password").val().trim();

    if(u.length < 3){
        $("#username").css("border","1px solid red");
        e.preventDefault();
        return;
    }

    if(p.length < 3){
        $("#password").css("border","1px solid red");
        e.preventDefault();
        return;
    }
});
</script>

</body>
</html>
