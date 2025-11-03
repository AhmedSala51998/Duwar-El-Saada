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
    .loader{
      position:fixed;
      inset:0;
      display:flex;
      justify-content:center;
      align-items:center;
      flex-direction:column;
      z-index:9999;
      background:#fff;
      transition:opacity .8s ease, visibility .8s ease;
    }
    .loader.hidden{opacity:0;visibility:hidden;}

    /* الدائرة */
    .circle{
      position:relative;
      width:160px;
      height:160px;
      border-radius:50%;
      border:4px solid rgba(255,127,50,0.2);
      display:flex;
      justify-content:center;
      align-items:center;
      animation:spin 3s linear infinite;
    }

    /* النص */
    .loader-text{
      color:#ff7f32;
      font-size:24px;
      font-weight:bold;
      text-align:center;
      text-shadow:0 0 10px rgba(255,127,50,0.8),
                  0 0 20px rgba(255,127,50,0.6);
      animation:pulse_loader 2s ease-in-out infinite;
      z-index:2;
    }

    /* البولز (نبضات قلب) */
    .pulse-dot{
      position:absolute;
      width:10px; height:10px;
      border-radius:50%;
      background:#ff7f32;
      opacity:0.8;
      transform:scale(0);
      animation:dotPulse 1.5s infinite ease-in-out;
    }

    /* توزيع البولز حول حافة الدائرة */
    .pulse-dot:nth-child(1){top:0; left:50%; animation-delay:0s;}
    .pulse-dot:nth-child(2){top:15%; right:0; animation-delay:0.1s;}
    .pulse-dot:nth-child(3){bottom:15%; right:0; animation-delay:0.2s;}
    .pulse-dot:nth-child(4){bottom:0; left:50%; animation-delay:0.3s;}
    .pulse-dot:nth-child(5){bottom:15%; left:0; animation-delay:0.4s;}
    .pulse-dot:nth-child(6){top:15%; left:0; animation-delay:0.5s;}

    /* الحركات */
    @keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
    @keyframes pulse_loader{
      0%,100%{transform:scale(1); filter:blur(0);}
      50%{transform:scale(1.1); filter:blur(1.5px);}
    }
    @keyframes dotPulse{
      0%{transform:scale(0); opacity:0;}
      50%{transform:scale(1); opacity:1;}
      100%{transform:scale(0); opacity:0;}
    }

    body.loading > *:not(.loader){
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

    .custom-navbar {
      padding-top: 0.05rem !important;   /* أقل ارتفاع ممكن بدون تشويه */
      padding-bottom: 0.05rem !important;
    }

    .custom-navbar .navbar-brand img {
      height:50px !important;  /* تصغير اللوجو ليكون أنسب مع الارتفاع الجديد */
      width:50px !important;
    }

    .custom-navbar .navbar-brand span {
      font-size: 1.15rem !important; /* أصغر شوية للتوازن */
      line-height: 1;
    }

    @media (max-width: 768px) {
      .custom-navbar {
        padding-top: 0.1rem !important;
        padding-bottom: 0.1rem !important;
      }
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      background: rgba(255, 106, 0, 0.1);
      color: #ff6a00;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 1.6rem;
      margin-right: 10px;
      position: relative;
      transition: transform 0.6s ease; /* لتدوير الأيقونة عند hover */
    }

    /* حركة التدوير عند hover */
    .stat-icon:hover {
      transform: rotate(360deg);
    }

    /* النبض المستمر */
    .stat-icon::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: rgba(255, 106, 0, 0.2);
      animation: pulse 1.5s infinite;
      top: 0;
      left: 0;
      z-index: -1;
    }

    /* تعريف النبض */
    @keyframes pulse {
      0% {
        transform: scale(1);
        opacity: 0.6;
      }
      50% {
        transform: scale(1.4);
        opacity: 0;
      }
      100% {
        transform: scale(1);
        opacity: 0.6;
      }
    }

    /* ترتيب العنوان مع الدائرة */
    .page-title {
      font-weight: 700;
      color: #2c3e50;
      display: flex;
      align-items: center;
      gap: 10px;
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
    <?php if(has_permission('settings.edit')): ?>
    <hr class="my-2">
    <h6 class="text-muted small px-2">الإعدادات</h6>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='roles.php'?'active':'' ?>" href="<?= BASE_URL ?>/roles.php">
        <i class="bi bi-shield-lock"></i> الأدوار
    </a>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='permissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/permissions.php">
        <i class="bi bi-person-check"></i> الصلاحيات
    </a>
    <?php endif ?>
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
        <?php if(has_permission('settings.edit')): ?>
        <hr class="my-2">
        <h6 class="text-muted small px-2">الإعدادات</h6>
        <?php if(has_permission('roles.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='roles.php'?'active':'' ?>" href="<?= BASE_URL ?>/roles.php">
            <i class="bi bi-shield-lock"></i> الأدوار
        </a>
        <?php endif ?>
        <?php if(has_permission('permissions.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='permissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/permissions.php">
            <i class="bi bi-person-check"></i> الصلاحيات
        </a>
        <?php endif ?>
        <?php endif ?>
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