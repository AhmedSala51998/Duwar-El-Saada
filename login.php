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
<link href="https://fonts.googleapis.com/css2?family=Amiri:ital@0;1&display=swap" rel="stylesheet">

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
/* Logo circle */
.logo-circle {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    position: relative;
}

/* توهج أكبر متحرك باستخدام ::after */

.logo-circle img {
    width: 140px;
}

/* Inputs */
.input-label {
    font-weight: 600;
    font-size: 14px;
}

.input-box {
    border: 1px solid #eee;
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
    margin-top:10px !important
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
        order: 2;
        padding: 30px 10px;
    }
    .right-side {
        order: 1;
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
        width: 120px;
        height: 120px;
    }
}

/* تعديل وضعية input-box */
.input-box {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-left: 35px; /* مساحة للأيقونة */
}

/* أيقونة الحالة (صح/تحذير) */
.input-box i.status-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    display: none;
}

/* حالة صح */
.input-box.success {
    border: 2px solid #28a745 !important;
}
.input-box.success i.status-icon {
    display: block;
    color: #28a745;
}

/* حالة خطأ */
.input-box.error {
    border: 2px solid #e74c3c !important;
}
.input-box.error i.status-icon {
    display: block;
    color: #e74c3c;
}

/* الرسالة التحذيرية */
.error-msg {
    font-size: 13px;
    color: #e74c3c;
    margin-top: -3px;
    display: none;
}

/* زر اللودينج */
.btn-loading {
    pointer-events: none;
    opacity: 0.7;
}

.input-box {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-left: 35px; /* مساحة للأيقونة */
    background: rgba(255,255,255,0.2); /* شفاف/فاتح يتناسب مع الخلفية */
    border-radius: 14px;
    transition: all 0.3s;
}

.input-box input {
    border: none;
    outline: none;
    background: transparent; /* خلي الخلفية للـinput شفافة */
    width: 100%;
    font-size: 15px;
    color: #333; /* نص أسود فاتح للقراءة */
}

/* Hover / Focus */
.input-box:hover,
.input-box:focus-within {
    border-color: #ff8a00;
    box-shadow: 0 3px 10px rgba(255,126,0,0.18);
}

/* حالة صح وخطأ */
.input-box.success {
    border: 2px solid #28a745 !important;
}

.input-box.error {
    border: 2px solid #e74c3c !important;
}
/* تطبيق الخط العثماني */
.logo-circle h3, 
.text-center p {
    font-family: 'Amiri', serif; /* الخط العثماني */
}

/* أو لو عايز فقط العنوان */
.text-center h3 {
    font-family: 'Amiri', serif;
    font-weight: 700; /* خط عريض للعنوان */
}

.text-center p {
    font-family: 'Amiri', serif;
    font-weight: 400; /* خط عادي للنص الصغير */
    font-size: 14px;
    color: #555;
}











/* Input Box تصميم احترافي */
.input-box {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px 12px 45px; /* مساحة للأيقونات على الشمال */
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.15); /* شفاف جزئي */
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.3s ease;
    backdrop-filter: blur(8px); /* تأثير ضبابي خفيف */
}

.input-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 15px;
    color: #222;
    padding: 0;
}

/* أيقونة الحالة (✔/⚠) */
.input-box i.status-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    display: none;
}

/* Hover / Focus */
.input-box:hover,
.input-box:focus-within {
    border-color: #ff8a00;
    box-shadow: 0 4px 15px rgba(255, 140, 0, 0.25);
}

/* حالة صح */
.input-box.success {
    border: 2px solid #28a745 !important;
}
.input-box.success i.status-icon {
    display: block;
    color: #28a745;
}

/* حالة خطأ */
.input-box.error {
    border: 2px solid #e74c3c !important;
}
.input-box.error i.status-icon {
    display: block;
    color: #e74c3c;
}

/* الرسالة التحذيرية */
.error-msg {
    font-size: 13px;
    color: #e74c3c;
    margin-top: 2px; /* قريبة جدًا من البوكس */
    line-height: 1.2;
    display: none;
}

/* زر تسجيل الدخول */
.btn-login {
    width: 100%;
    border: none;
    padding: 14px;
    background: linear-gradient(135deg, #ff8a00, #ff6a00);
    border-radius: 16px;
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    box-shadow: 0 5px 18px rgba(255,102,0,0.25);
    transition: all 0.3s ease;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255,102,0,0.32);
}

.btn-loading {
    pointer-events: none;
    opacity: 0.7;
}


.input-box {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px 12px 40px; /* مساحة للأيقونة */
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    backdrop-filter: blur(8px);
}

.input-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 15px;
    color: #222;
    padding: 0;
}

/* أيقونة على اليسار */
.input-box i.fa {
    position: absolute;
    left: 12px;
    font-size: 18px;
    color: #ff7a00;
}

/* Hover / Focus */
.input-box:hover,
.input-box:focus-within {
    border-color: #ff8a00;
    box-shadow: 0 4px 15px rgba(255, 140, 0, 0.25);
}

/* حالة صح وخطأ باستخدام Bootstrap */
.was-validated .input-box input:valid ~ .valid-feedback {
    display: block;
}

.was-validated .input-box input:invalid ~ .invalid-feedback {
    display: block;
}

/* الانبوتات */
.form-control {
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.3s;
    backdrop-filter: blur(8px);
}

/* مساحة للأيقونة على اليمين */
.form-control.pe-5 {
    padding-right: 40px;
}

/* hover / focus */
.form-control:focus {
    border-color: #ff8a00;
    box-shadow: 0 4px 15px rgba(255,140,0,0.25);
}

.input-with-icon {
    padding-right: 2.5rem; /* يترك مساحة كافية للأيقونة */
}

.icon-right {
    position: absolute;
    right: 12px;
    top: 50%; /* الوضع الطبيعي */
    transform: translateY(-50%);
    color: #ff7a00;
    pointer-events: none;
    transition: top 0.3s ease; /* حركة سلسة */
}

/* لما الفورم فيه was-validated، نحرك الإيقونات */
.was-validated .icon-right {
    top: 32%;
}

/* لمنع أي حركة بسبب الفاليديشن */
.input-with-icon:focus,
.input-with-icon:valid,
.input-with-icon:invalid {
    padding-right: 2.5rem; /* نفس الـ padding ثابت دائمًا */
}

.server-error {
    background-color: #ffe5e0; /* أحمر فاتح */
    color: #c70000;           /* أحمر نص */
    border: 1px solid #ff7a00; /* حدود بسيطة برتقالية */
    border-radius: 8px;        /* حواف مدورة */
    padding: 10px 15px;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 10px
}


.theme-toggle {
    position: fixed;
    top: 20px;
    left: 20px;
    background: #ff8a00;
    color: white;
    border: none;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    z-index: 99999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    transition: 0.3s;
}

.theme-toggle:hover {
    transform: scale(1.12);
}



/* ======================= */
/*      DARK MODE STYLE    */
/* ======================= */

.dark-mode body {
    background: linear-gradient(135deg, #1e1e1e, #111);
    color: #ddd;
}

.dark-mode .login-card-advanced {
    background: rgba(30,30,30,0.7);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.dark-mode .logo-circle {
    background: rgba(255,255,255,0.05);
}

.dark-mode .text-center h3 {
    color: #fff !important;
}

.dark-mode p,
.dark-mode small {
    color: #bbb !important;
}

/* INPUTS */
.dark-mode .form-control {
    background: rgba(255,255,255,0.08);
    color: #f5f5f5;
    border: 1px solid rgba(255,255,255,0.15);
}

.dark-mode .form-control::placeholder {
    color: #aaa;
}

.dark-mode .input-with-icon {
    color: #fff;
}

.dark-mode .icon-right {
    color: #ff9c40;
}

/* INPUT STATES */
.dark-mode .form-control:focus {
    border-color: #ff9c40;
    box-shadow: 0 4px 15px rgba(255, 156, 64, 0.25);
}

/* BUTTON */
.dark-mode .btn-login {
    background: linear-gradient(135deg, #ff7700, #ff5500);
    box-shadow: 0 4px 15px rgba(255,100,0,0.3);
}

/* ERROR BOX */
.dark-mode .server-error {
    background-color: #4a1d1d;
    color: #ffb3b3;
    border-color: #ff5500;
}

/* DARK MODE RIGHT SIDE BG */
.dark-mode .right-side {
    background: transparent;
}
.dark-mode .login-card-advanced {
    background: rgba(20, 20, 20, 0.55) !important; /* داكن نصف شفاف */
    border-radius: 25px;
    border: 1px solid rgba(255, 255, 255, 0.08);   /* حدود خفيفة جدًا */
    backdrop-filter: blur(20px) saturate(160%);    /* زجاجي فاخر */
    -webkit-backdrop-filter: blur(20px) saturate(160%);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.6);     /* ظل داكن قوي */
    animation: fadeUp 0.8s ease forwards;
}
.dark-mode .left-side {
    background: linear-gradient(135deg, #0f0f0f, #1c1c1c) !important;
    backdrop-filter: blur(4px) saturate(140%);
    -webkit-backdrop-filter: blur(4px) saturate(140%);
}

</style>

</head>
<body>
<button id="themeToggle" class="theme-toggle">
    <i class="fas fa-moon"></i>
</button>
<div class="page-wrapper">

    <!-- RIGHT -->
    <div class="left-side">

        <div class="login-card-advanced">

            <div class="text-center mb-4">
                <div class="logo-circle mb-3">
                    <img src="<?= esc(getSystemSettings('secondary_logo') ?: '/assets/logo.png') ?>">
                </div>
                <h3 class="fw-bold text-dark">مطعم دوار السعادة</h3>
                <p class="text-muted small">تسجيل الدخول للنظام</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="server-error mt-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" class="needs-validation" novalidate method="post">
                <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

                <div class="mb-3 position-relative">
                    <input type="text" name="username" id="username" class="form-control input-with-icon" placeholder="اسم المستخدم" required minlength="3">
                    <div class="valid-feedback">يبدو جيدًا!</div>
                    <div class="invalid-feedback">اسم المستخدم يجب أن يكون 3 أحرف على الأقل</div>
                    <i class="fa fa-user icon-right"></i>
                </div>

                <div class="mb-3 position-relative">
                    <input type="password" name="password" id="password" class="form-control input-with-icon" placeholder="كلمة المرور" required minlength="3">
                    <div class="valid-feedback">يبدو جيدًا!</div>
                    <div class="invalid-feedback">كلمة المرور يجب أن تكون 3 أحرف على الأقل</div>
                    <i class="fa fa-lock icon-right"></i>
                </div>

                <button type="submit" class="btn-login w-100" id="loginBtn">تسجيل الدخول</button>
            </form>

            <p class="text-center mt-3 text-muted small">
              © جميع الحقوق محفوظة لدى مطعم دوار السعادة 2025            
            </p>

        </div>

    </div>

    <!-- LEFT -->
    <div class="right-side">
        <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.5/dist/dotlottie-wc.js" type="module"></script>

        <dotlottie-wc
          src="https://lottie.host/5d4e2813-f7d2-40fd-9136-c6b747b616d6/QtP93VYwuQ.lottie"
          style="width: 600px;height: 600px"
          autoplay
          loop
        ></dotlottie-wc>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')

    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                } else {
                    let btn = document.getElementById('loginBtn');
                    btn.classList.add('btn-loading');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري تسجيل الدخول';
                }

                form.classList.add('was-validated')
            }, false)
        })
})();
</script>
<script>
// استرجاع الوضع
if (localStorage.getItem("theme") === "dark") {
    document.documentElement.classList.add("dark-mode");
    document.getElementById("themeToggle").innerHTML = '<i class="fas fa-sun"></i>';
}

// الزر
document.getElementById("themeToggle").addEventListener("click", function () {
    document.documentElement.classList.toggle("dark-mode");

    if (document.documentElement.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
        this.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        localStorage.setItem("theme", "light");
        this.innerHTML = '<i class="fas fa-moon"></i>';
    }
});

</script>


</body>
</html>
