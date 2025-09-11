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
    /* اللودر */
    .loader {
      position:fixed;
      inset:0;
      background:#121212;
      display:flex;
      justify-content:center;
      align-items:center;
      z-index:1000;
      flex-direction:column;
      transition:opacity .8s ease, visibility .8s ease;
    }
    .loader.hidden {
      opacity:0;
      visibility:hidden;
    }

    /* الدائرة */
    .circle {
      position:relative;
      width:150px; height:150px;
      border-radius:50%;
      border:3px solid rgba(255,127,50,0.2);
      animation: spin 2s linear infinite;
      margin-bottom:20px;
    }
    .circle::before {
      content:"";
      position:absolute;
      top:-8px; left:50%;
      width:16px; height:16px;
      background:#ff7f32;
      border-radius:50%;
      box-shadow:0 0 15px #ff7f32, 0 0 30px rgba(255,127,50,0.6);
      transform:translateX(-50%);
    }

    /* النص */
    .loader-text {
      font-size:28px;
      font-weight:bold;
      color:#ff7f32;
      text-shadow:0 0 10px rgba(255,127,50,0.8), 
                  0 0 20px rgba(255,127,50,0.6),
                  0 0 40px rgba(255,127,50,0.4);
      animation:pulseBlur 2s infinite ease-in-out;
    }

    @keyframes spin {
      from {transform:rotate(0deg);}
      to   {transform:rotate(360deg);}
    }

    @keyframes pulseBlur {
      0%,100% {filter:blur(0px); transform:scale(1);}
      50%     {filter:blur(2px); transform:scale(1.2);}
    }

    /* إخفاء باقي الصفحة أثناء التحميل */
    body.loading > *:not(.loader) {
      opacity:0;
      pointer-events:none;
    }

    /* تمييز الصفحة النشطة */
    .sidebar-link.active,
    .nav-link.active {
      background-color: #ff6600; /* لون الهوفر بتاعك */
      color: #fff !important;
      border-radius: 6px;
    }
    .custom-navbar {
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0,0,0,0.08); /* ✅ أسود خفيف جدًا */
      padding: .7rem 1rem;
    }
    /* لون البرتقالي */
    .text-orange { color: #ff6a00 !important; }

    /* الدور */
    .role-badge {
      background: #fff3e6;
      color: #ff6a00;
      font-weight: 600;
      border-radius: 50px;
      padding: .5rem 1rem;
      box-shadow: 0 2px 6px rgba(255,106,0,.2);
    }

    /* روابط */
    .navbar .nav-link {
      font-weight: 500;
      padding: .6rem 1.2rem;
      border-radius: 12px;
      color: #555 !important;
      transition: all .2s ease;
    }
    .navbar .nav-link:hover {
      background: rgba(255,106,0,.08);
      color: #ff6a00 !important;
    }
    .navbar .nav-link.active {
      background: rgba(255,106,0,.15);
      color: #ff6a00 !important;
      font-weight: 600;
    }

    /* زر خروج */
    .btn-logout {
      background: linear-gradient(135deg,#ff6a00,#ff944d);
      color: #fff;
      font-weight: 600;
      padding: .6rem 1.4rem;
      border-radius: 50px;
      box-shadow: 0 4px 12px rgba(255,106,0,.3);
      transition: all .3s ease;
    }
    .btn-logout:hover {
      background: linear-gradient(135deg,#e65a00,#ff7a1f);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255,106,0,.4);
      color: #fff !important;
    }
  </style>
</head>
<body>
  <div class="loader">
    <div class="circle"></div>
    <div class="loader-text">دوار السعادة</div>
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
      <img src="<?= BASE_URL ?>/assets/logo.svg" width="42" height="42" alt="logo" class="rounded shadow-sm">
      <span class="fs-5"><?= esc(APP_NAME) ?></span>
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
    // إخفاء اللودر بعد 3 ثواني وإظهار المحتوى
    window.addEventListener('load', () => {
      setTimeout(() => {
        document.querySelector('.loader').classList.add('hidden');
        document.getElementById('page-wrapper').style.opacity = '1';
      }, 3000);
    });
  </script>