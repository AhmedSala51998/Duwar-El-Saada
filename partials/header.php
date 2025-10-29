<?php
require __DIR__.'/../config/config.php'; 
require_auth();

// تحديد اسم الصفحة الحالية
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>


    /* -----------------------------
   ⚙️ Loader Style (احترافي متطور)
------------------------------ */
.loader {
  position: fixed;
  inset: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background: linear-gradient(135deg, #fff8f3 0%, #ffffff 100%);
  z-index: 9999;
  transition: opacity 0.8s ease, visibility 0.8s ease;
}
.loader.hidden {
  opacity: 0;
  visibility: hidden;
}

.circle {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  border: 3px solid rgba(255, 128, 0, 0.25);
  display: flex;
  justify-content: center;
  align-items: center;
  animation: spin 3.2s linear infinite;
}

.loader-text {
  color: #ff6a00;
  font-size: 22px;
  font-weight: 700;
  text-shadow: 0 0 6px rgba(255, 128, 0, 0.6);
  animation: pulse 2s ease-in-out infinite;
  letter-spacing: 1px;
}

.pulse-dot {
  position: absolute;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #ff6a00;
  opacity: 0.8;
  transform: scale(0);
  animation: dotPulse 1.5s infinite ease-in-out;
}
.pulse-dot:nth-child(2){ top:0; left:50%; animation-delay:0s;}
.pulse-dot:nth-child(3){ top:15%; right:0; animation-delay:0.1s;}
.pulse-dot:nth-child(4){ bottom:15%; right:0; animation-delay:0.2s;}
.pulse-dot:nth-child(5){ bottom:0; left:50%; animation-delay:0.3s;}
.pulse-dot:nth-child(6){ bottom:15%; left:0; animation-delay:0.4s;}
.pulse-dot:nth-child(7){ top:15%; left:0; animation-delay:0.5s;}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes pulse {
  0%, 100% { transform: scale(1); filter: blur(0); }
  50% { transform: scale(1.15); filter: blur(1px); }
}
@keyframes dotPulse {
  0%, 100% { transform: scale(0); opacity: 0; }
  50% { transform: scale(1); opacity: 1; }
}

/* -----------------------------
   🌈 Navbar (مظهر زجاجي أنيق)
------------------------------ */
.custom-navbar {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 0.5rem 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}
.custom-navbar:hover {
  background: rgba(255, 255, 255, 0.95);
}

.navbar-brand img {
  height: 65px !important;
  width: 65px !important;
  transition: transform 0.3s ease;
}
.navbar-brand img:hover {
  transform: rotate(-3deg) scale(1.05);
}

.navbar-brand span {
  font-family: 'Scheherazade New', serif;
  font-size: 1.4rem;
  font-weight: 700;
  color: #ff6a00;
}

/* -----------------------------
   🔶 الروابط في Navbar
------------------------------ */
.navbar .nav-link {
  font-weight: 500;
  padding: 0.6rem 1.1rem;
  border-radius: 12px;
  color: #555 !important;
  transition: all 0.25s ease;
}
.navbar .nav-link:hover {
  background: rgba(255, 106, 0, 0.08);
  color: #ff6a00 !important;
}
.navbar .nav-link.active {
  background: rgba(255, 106, 0, 0.15);
  color: #ff6a00 !important;
  font-weight: 600;
}

/* -----------------------------
   🧿 زر تسجيل الخروج
------------------------------ */
.btn-logout {
  background: linear-gradient(135deg, #ff6a00, #ff944d);
  color: #fff;
  font-weight: 600;
  padding: 0.6rem 1.4rem;
  border-radius: 50px;
  box-shadow: 0 4px 12px rgba(255, 106, 0, 0.25);
  transition: all 0.3s ease;
}
.btn-logout:hover {
  background: linear-gradient(135deg, #e65a00, #ff7a1f);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(255, 106, 0, 0.35);
  color: #fff !important;
}

/* -----------------------------
   🎖️ بادج الدور
------------------------------ */
.role-badge {
  background: #fff3e6;
  color: #ff6a00;
  font-weight: 600;
  border-radius: 50px;
  padding: 0.45rem 1rem;
  box-shadow: 0 2px 6px rgba(255, 106, 0, 0.15);
}

/* -----------------------------
   📚 القائمة الجانبية (Side)
------------------------------ */
.sidebar-link {
  display: block;
  color: #555;
  padding: 0.55rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.25s ease;
}
.sidebar-link:hover {
  background: rgba(255, 106, 0, 0.08);
  color: #ff6a00;
  transform: translateX(-2px);
}
.sidebar-link.active {
  background: linear-gradient(90deg, #ff6a00 0%, #ff944d 100%);
  color: #fff !important;
  box-shadow: 0 3px 10px rgba(255, 106, 0, 0.25);
}

/* -----------------------------
   🖥️ Sidebar in desktop
------------------------------ */
aside {
  background: #fff;
  box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.05);
}
aside .text-muted {
  font-weight: 600;
  color: #999 !important;
}

/* -----------------------------
   ⚡ General
------------------------------ */
body {
  font-family: 'Cairo', sans-serif;
  background-color: #fafafa;
}
.flash {
  background: #fff4e6;
  color: #ff6a00;
  border: 1px solid #ffd6b3;
  border-radius: 10px;
  padding: 0.75rem 1.25rem;
  box-shadow: 0 3px 6px rgba(255, 106, 0, 0.1);
}



  </style>
  <link href="https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="loader">
    <div class="circle">
      <div class="loader-text">دوار السعادة</div>

      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
    </div>
  </div>
<div id="page-wrapper" style="opacity:0; transition:opacity .8s ease;">
<nav class="navbar navbar-expand-lg sticky-top custom-navbar">
  <div class="container-fluid">

    <!-- زر القائمة للموبايل -->
    <button class="btn d-md-none me-2 text-orange fs-3 border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
      <i class="bi bi-list"></i>
    </button>

    <!-- اللوجو -->
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-orange" href="<?= BASE_URL ?>/home.php">
      <img src="<?= BASE_URL ?>/assets/logo.png" width="65" height="65" alt="logo" class="rounded shadow-sm">
      <span class="fs-5" style="font-family: 'Scheherazade New', serif; font-size: 1.4rem; font-weight: 700;">
        <?= esc(APP_NAME) ?>
      </span>
    </a>

    <!-- زرار القائمة -->
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- عناصر النافبار -->
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-3">

        <!-- الدور -->
        <li class="nav-item">
          <span class="badge role-badge">
            <i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?>
          </span>
        </li>

        <!-- المستخدمون -->
        <li class="nav-item">
          <a class="nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php">
            <i class="bi bi-people me-1"></i> المستخدمون
          </a>
        </li>

        <!-- خروج -->
        <li class="nav-item">
          <a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php">
            <i class="bi bi-box-arrow-right me-1"></i> خروج
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav></div>

<!-- القائمة الجانبية في الموبايل (Offcanvas) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">القائمة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="sidebar-link d-block mb-2 <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
    <a class="sidebar-link d-block <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
    <!--<a class="sidebar-link d-block mb-2 <?= $current_page=='gov_fees.php'?'active':'' ?>" href="<?= BASE_URL ?>/gov_fees.php"><i class="bi bi-file-earmark-text"></i> الرسوم الحكومية</a>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='subscriptions.php'?'active':'' ?>" href="<?= BASE_URL ?>/subscriptions.php"><i class="bi bi-journal-bookmark"></i> الاشتراكات والخدمات</a>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='rentals.php'?'active':'' ?>" href="<?= BASE_URL ?>/rentals.php"><i class="bi bi-house-door"></i> الإيجارات</a>-->
    <a class="sidebar-link d-block <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
    <a class="sidebar-link d-block <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar في الديسكتوب -->
    <aside class="col-lg-2 col-md-3 border-end min-vh-100 d-none d-md-block">
      <div class="p-3">
        <div class="text-muted small mb-2">القائمة</div>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
        <!--<a class="sidebar-link d-block mb-2 <?= $current_page=='gov_fees.php'?'active':'' ?>" href="<?= BASE_URL ?>/gov_fees.php"><i class="bi bi-file-earmark-text"></i> الرسوم الحكومية</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='subscriptions.php'?'active':'' ?>" href="<?= BASE_URL ?>/subscriptions.php"><i class="bi bi-journal-bookmark"></i> الاشتراكات والخدمات</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='rentals.php'?'active':'' ?>" href="<?= BASE_URL ?>/rentals.php"><i class="bi bi-house-door"></i> الإيجارات</a>-->
        <a class="sidebar-link d-block <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
        <a class="sidebar-link d-block <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
      </div>
    </aside>

    <!-- المحتوى -->
    <main class="col-12 col-md-9 col-lg-10 p-4">
      <?php if($m = flash('msg')): ?>
        <div class="flash mb-3"><?= esc($m) ?></div>
      <?php endif; ?>

  <script>
    window.addEventListener('load', () => {
      const loader = document.querySelector('.loader');
      const page = document.getElementById('page-wrapper');

      // إخفاء اللودر
      loader.classList.add('hidden');

      // إظهار المحتوى تدريجيًا
      page.style.opacity = '1';

      // لو فيه Toast
      const toastEl = document.getElementById('liveToast');
      if(toastEl){
        const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
        toast.show();
      }
    });
  </script>