<?php
require __DIR__.'/../config/config.php'; 
require_auth();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc(APP_NAME) ?></title>
  
  <!-- خطوط وأيقونات -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- الخط العربي المميز -->
  <link href="https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@700&display=swap" rel="stylesheet">

  <style>
    /* الخط الأساسي */
    body {
      font-family: 'Cairo', sans-serif;
      background: linear-gradient(135deg, #fffaf6, #fefcfb);
      color: #333;
      overflow-x: hidden;
    }

    /* تأثير خلفية زجاجية */
    .glass-bg {
      background: rgba(255,255,255,0.75);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.25);
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    /* اللودر */
    .loader {
      position: fixed;
      inset: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      z-index: 9999;
      background: radial-gradient(circle at center, #fff 0%, #fef6f2 100%);
      transition: opacity .8s ease, visibility .8s ease;
    }
    .loader.hidden {opacity: 0; visibility: hidden;}

    .circle {
      position: relative;
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 3px solid rgba(255, 102, 0, 0.2);
      display: flex;
      justify-content: center;
      align-items: center;
      animation: spin 3s linear infinite;
      box-shadow: 0 0 20px rgba(255, 102, 0, 0.3);
    }
    .loader-text {
      color: #ff6a00;
      font-size: 1.6rem;
      font-weight: 700;
      animation: pulse 2s ease-in-out infinite;
      text-shadow: 0 0 12px rgba(255, 106, 0, 0.7);
    }

    .pulse-dot {
      position: absolute;
      width: 10px; height: 10px;
      border-radius: 50%;
      background: #ff6a00;
      opacity: 0.8;
      transform: scale(0);
      animation: dotPulse 1.5s infinite ease-in-out;
    }
    .pulse-dot:nth-child(1){top:0; left:50%; animation-delay:0s;}
    .pulse-dot:nth-child(2){top:15%; right:0; animation-delay:0.1s;}
    .pulse-dot:nth-child(3){bottom:15%; right:0; animation-delay:0.2s;}
    .pulse-dot:nth-child(4){bottom:0; left:50%; animation-delay:0.3s;}
    .pulse-dot:nth-child(5){bottom:15%; left:0; animation-delay:0.4s;}
    .pulse-dot:nth-child(6){top:15%; left:0; animation-delay:0.5s;}

    @keyframes spin {0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
    @keyframes pulse {0%,100%{transform:scale(1);}50%{transform:scale(1.15);}}
    @keyframes dotPulse {0%{transform:scale(0);opacity:0;}50%{transform:scale(1);opacity:1;}100%{transform:scale(0);opacity:0;}}

    body.loading > *:not(.loader) {
      opacity: 0;
      pointer-events: none;
    }

    /* النافبار */
    .custom-navbar {
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(15px);
      border-bottom: 1px solid rgba(0,0,0,0.05);
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      padding: .4rem 1rem;
    }
    .custom-navbar .navbar-brand img {
      height: 55px; width: 55px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(255,106,0,.3);
    }
    .custom-navbar .navbar-brand span {
      font-family: 'Scheherazade New', serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: #ff6600;
    }

    .navbar .nav-link {
      font-weight: 500;
      padding: .6rem 1rem;
      border-radius: 12px;
      color: #555 !important;
      transition: all .2s ease;
    }
    .navbar .nav-link:hover {
      background: rgba(255,106,0,.1);
      color: #ff6a00 !important;
      transform: translateY(-1px);
    }
    .navbar .nav-link.active {
      background: rgba(255,106,0,.2);
      color: #ff6a00 !important;
      font-weight: 600;
      box-shadow: 0 3px 6px rgba(255,106,0,.2);
    }

    /* زر الخروج */
    .btn-logout {
      background: linear-gradient(135deg,#ff6a00,#ff944d);
      color: #fff;
      font-weight: 600;
      padding: .6rem 1.2rem;
      border-radius: 50px;
      box-shadow: 0 4px 10px rgba(255,106,0,.3);
      transition: all .3s ease;
    }
    .btn-logout:hover {
      background: linear-gradient(135deg,#e85c00,#ff7f2a);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255,106,0,.4);
      color: #fff !important;
    }

    /* الشارة */
    .role-badge {
      background: #fff4eb;
      color: #ff6a00;
      font-weight: 600;
      border-radius: 50px;
      padding: .45rem 1rem;
      box-shadow: 0 2px 8px rgba(255,106,0,.15);
    }

    /* القائمة الجانبية */
    .sidebar-link {
      display: block;
      padding: .6rem 1rem;
      border-radius: 10px;
      color: #555;
      font-weight: 500;
      transition: all .2s ease;
      text-decoration: none;
    }
    .sidebar-link:hover {
      background: rgba(255,106,0,.08);
      color: #ff6a00;
      transform: translateX(-4px);
    }
    .sidebar-link.active {
      background: rgba(255,106,0,.2);
      color: #ff6a00;
      font-weight: 600;
      box-shadow: 0 3px 8px rgba(255,106,0,.15);
    }

    /* الأنيميشن العام */
    [data-animate] {
      opacity: 0;
      transform: translateY(15px);
      transition: all 0.6s ease;
    }
    [data-animate].visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>

<body class="loading">
  <!-- شاشة التحميل -->
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
<nav class="navbar navbar-expand-lg sticky-top custom-navbar glass-bg">
  <div class="container-fluid">
    <button class="btn d-md-none me-2 text-orange fs-3 border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
      <i class="bi bi-list"></i>
    </button>

    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/home.php">
      <img src="<?= BASE_URL ?>/assets/logo.png" alt="logo">
      <span><?= esc(APP_NAME) ?></span>
    </a>

    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-3">
        <li class="nav-item">
          <span class="badge role-badge"><i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?></span>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php">
            <i class="bi bi-people me-1"></i> المستخدمون
          </a>
        </li>
        <li class="nav-item">
          <a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php">
            <i class="bi bi-box-arrow-right me-1"></i> خروج
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>

<!-- قائمة الموبايل -->
<div class="offcanvas offcanvas-start glass-bg" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title fw-bold text-orange">القائمة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
    <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
    <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
    <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
    <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
    <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
    <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <aside class="col-lg-2 col-md-3 border-end min-vh-100 d-none d-md-block glass-bg p-3">
      <div class="text-muted small mb-2 fw-bold">القائمة</div>
      <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
      <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
      <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
      <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
      <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
      <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
      <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
    </aside>

    <main class="col-12 col-md-9 col-lg-10 p-4" data-animate>
      <?php if($m = flash('msg')): ?>
        <div class="flash mb-3"><?= esc($m) ?></div>
      <?php endif; ?>
    </main>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    const loader = document.querySelector('.loader');
    const page = document.getElementById('page-wrapper');
    loader.classList.add('hidden');
    page.style.opacity = '1';

    // أنيميشن الظهور
    document.querySelectorAll('[data-animate]').forEach(el=>{
      setTimeout(()=> el.classList.add('visible'), 500);
    });
  });
</script>
</body>
</html>
