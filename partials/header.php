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
  /* ===============================
    ⚡ STYLE ULTRA-PRO 2025 EDITION
    تصميم عصري احترافي بدون تغيير الهيكل
  ================================== */

  :root {
    --orange: #ff6a00;
    --orange-light: #ff944d;
    --bg: #f7f8fc;
    --text: #444;
    --radius: 14px;
    --shadow: 0 6px 20px rgba(0,0,0,0.05);
  }

  /* -----------------------------
    ⚙️ Loader Modern Look
  ------------------------------ */
  .loader {
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background: radial-gradient(circle at center, #fff 0%, #fff8f3 100%);
    z-index: 9999;
    transition: opacity 0.8s ease, visibility 0.8s ease;
  }
  .loader.hidden { opacity: 0; visibility: hidden; }

  .circle {
    position: relative;
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, var(--orange), var(--orange-light), var(--orange));
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 6px), #000 0);
    animation: spin 2s linear infinite;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .loader-text {
    color: var(--orange);
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-shadow: 0 0 12px rgba(255,106,0,0.6);
    animation: glow 2s ease-in-out infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  @keyframes glow {
    0%,100% { opacity: 1; text-shadow: 0 0 8px rgba(255,106,0,0.6);}
    50% { opacity: .7; text-shadow: 0 0 16px rgba(255,106,0,0.8);}
  }

  /* -----------------------------
    🌈 Navbar (Glassmorphic + Glow)
  ------------------------------ */
  .custom-navbar {
    background: rgba(255,255,255,0.8);
    backdrop-filter: blur(12px) saturate(160%);
    border-bottom: 1px solid rgba(255,106,0,0.08);
    padding: 0.6rem 1.2rem;
    box-shadow: 0 5px 25px rgba(255,106,0,0.08);
    transition: all 0.3s ease;
  }
  .custom-navbar:hover {
    background: rgba(255,255,255,0.92);
  }

  .navbar-brand img {
    height: 70px !important;
    width: 70px !important;
    border-radius: 16px;
    box-shadow: 0 3px 10px rgba(255,106,0,0.25);
    transition: transform .4s ease;
  }
  .navbar-brand img:hover {
    transform: scale(1.05) rotate(-2deg);
  }
  .navbar-brand span {
    color: var(--orange);
    font-size: 1.5rem !important;
    font-weight: 800;
    font-family: 'Scheherazade New', serif;
  }

  /* -----------------------------
    🔸 Navbar Links
  ------------------------------ */
  .navbar .nav-link {
    font-weight: 500;
    padding: .55rem 1.2rem;
    border-radius: var(--radius);
    color: var(--text) !important;
    transition: all .25s ease;
  }
  .navbar .nav-link:hover {
    background: rgba(255,106,0,.1);
    color: var(--orange) !important;
  }
  .navbar .nav-link.active {
    background: linear-gradient(90deg, var(--orange), var(--orange-light));
    color: #fff !important;
    box-shadow: 0 3px 12px rgba(255,106,0,.25);
  }

  /* -----------------------------
    🎖️ Role Badge
  ------------------------------ */
  .role-badge {
    background: linear-gradient(135deg,#fff3e6,#fff);
    color: var(--orange);
    font-weight: 600;
    border-radius: 50px;
    padding: .5rem 1rem;
    box-shadow: inset 0 0 8px rgba(255,106,0,0.1), 0 2px 8px rgba(255,106,0,0.1);
  }

  /* -----------------------------
    🚪 Logout Button
  ------------------------------ */
  .btn-logout {
    background: linear-gradient(135deg,var(--orange),var(--orange-light));
    color: #fff !important;
    font-weight: 600;
    padding: .6rem 1.4rem;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(255,106,0,.3);
    transition: all .3s ease;
  }
  .btn-logout:hover {
    background: linear-gradient(135deg,#e65a00,#ff7a1f);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255,106,0,.35);
  }

  /* -----------------------------
    📑 Sidebar Links (Glass Style)
  ------------------------------ */
  .sidebar-link {
    display: block;
    padding: .65rem 1rem;
    color: #555;
    border-radius: var(--radius);
    text-decoration: none;
    font-weight: 500;
    transition: all .25s ease;
  }
  .sidebar-link:hover {
    background: rgba(255,106,0,0.08);
    color: var(--orange);
    transform: translateX(-2px);
  }
  .sidebar-link.active {
    background: linear-gradient(90deg,var(--orange),var(--orange-light));
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(255,106,0,.25);
  }

  /* -----------------------------
    🧭 Sidebar container
  ------------------------------ */
  aside {
    background: #fff;
    border-right: 1px solid rgba(0,0,0,0.05);
    box-shadow: inset -2px 0 8px rgba(0,0,0,0.02);
  }
  aside .text-muted {
    font-weight: 600;
    color: #999 !important;
    letter-spacing: .3px;
  }

  /* -----------------------------
    ⚡ Page background + flash
  ------------------------------ */
  body {
    font-family: 'Cairo', sans-serif;
    background: var(--bg);
  }
  .flash {
    background: #fff7ef;
    color: var(--orange);
    border: 1px solid #ffd4b0;
    border-radius: var(--radius);
    padding: 0.75rem 1.25rem;
    box-shadow: 0 3px 10px rgba(255,106,0,0.1);
    font-weight: 600;
  }

  /* -----------------------------
    🔥 Offcanvas (موبايل)
  ------------------------------ */
  .offcanvas {
    background: #fff;
    border-right: 1px solid rgba(255,106,0,0.08);
  }
  .offcanvas-title {
    color: var(--orange);
    font-weight: 700;
  }

  /* -----------------------------
    🌀 Transitions + Animations
  ------------------------------ */
  #page-wrapper { opacity: 0; transition: opacity .8s ease; }
  body.loading > *:not(.loader) { opacity: 0; pointer-events: none; }

  /* Mobile tweak */
  @media (max-width: 768px) {
    .navbar-brand img { height: 55px !important; width: 55px !important; }
    .navbar-brand span { font-size: 1.2rem !important; }
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