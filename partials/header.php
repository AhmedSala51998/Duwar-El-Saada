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

  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: linear-gradient(135deg, #fff8f2, #fff);
      color: #333;
      transition: background .3s ease;
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
      background: linear-gradient(135deg, #fff, #fff7f0);
      transition: opacity .8s ease, visibility .8s ease;
    }
    .loader.hidden { opacity: 0; visibility: hidden; }

    .circle {
      width: 140px;
      height: 140px;
      border: 4px solid rgba(255,106,0,0.2);
      border-top-color: #ff6a00;
      border-radius: 50%;
      animation: spin 1.8s linear infinite;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    .loader-text {
      position: absolute;
      font-weight: 700;
      font-size: 1.2rem;
      color: #ff6a00;
      animation: pulse 2s infinite ease-in-out;
    }
    @keyframes pulse {
      0%,100% { opacity: .9; transform: scale(1); }
      50% { opacity: 1; transform: scale(1.08); }
    }

    /* النافبار */
    .custom-navbar {
      background: rgba(255,255,255,0.75);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(0,0,0,0.05);
      padding: .6rem 1.2rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .navbar-brand img {
      width: 55px; height: 55px; border-radius: 12px;
    }
    .navbar-brand span {
      font-size: 1.3rem;
      font-weight: 700;
      color: #ff6a00;
    }

    /* الروابط */
    .navbar .nav-link {
      color: #555 !important;
      font-weight: 500;
      border-radius: 10px;
      padding: .5rem 1rem;
      transition: all .3s ease;
    }
    .navbar .nav-link:hover {
      background: rgba(255,106,0,0.08);
      color: #ff6a00 !important;
    }
    .navbar .nav-link.active {
      background: rgba(255,106,0,0.12);
      color: #ff6a00 !important;
      font-weight: 600;
    }

    /* زر الخروج */
    .btn-logout {
      background: linear-gradient(135deg,#ff6a00,#ff944d);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: .55rem 1.4rem;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(255,106,0,0.25);
      transition: all .3s ease;
    }
    .btn-logout:hover {
      background: linear-gradient(135deg,#e65a00,#ff7a1f);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255,106,0,0.35);
    }

    /* الدور */
    .role-badge {
      background: #fff6ed;
      color: #ff6a00;
      font-weight: 600;
      padding: .45rem 1rem;
      border-radius: 50px;
      box-shadow: 0 2px 6px rgba(255,106,0,0.15);
    }

    /* القائمة الجانبية */
    .sidebar-link {
      display: block;
      color: #555;
      text-decoration: none;
      padding: .6rem 1rem;
      border-radius: 10px;
      transition: all .25s ease;
      font-weight: 500;
    }
    .sidebar-link:hover {
      background: rgba(255,106,0,0.07);
      color: #ff6a00;
      transform: translateX(-3px);
    }
    .sidebar-link.active {
      background: rgba(255,106,0,0.12);
      color: #ff6a00;
      font-weight: 600;
    }

    aside {
      background: #fff;
      box-shadow: 0 0 20px rgba(0,0,0,0.04);
    }

    main {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 0 15px rgba(0,0,0,0.03);
      margin: 1rem;
      padding: 2rem;
      animation: fadeIn .8s ease;
    }

    @keyframes fadeIn { from {opacity:0; transform:translateY(10px);} to {opacity:1; transform:none;} }

  </style>
</head>
<body class="loading">

  <!-- Loader -->
  <div class="loader">
    <div class="circle"></div>
    <div class="loader-text">جارٍ التحميل...</div>
  </div>

  <div id="page-wrapper" style="opacity:0; transition:opacity .8s ease;">
    <nav class="navbar navbar-expand-lg sticky-top custom-navbar">
      <div class="container-fluid">
        <button class="btn d-md-none text-orange fs-3 border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
          <i class="bi bi-list"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/home.php">
          <img src="<?= BASE_URL ?>/assets/logo.png" alt="logo">
          <span><?= esc(APP_NAME) ?></span>
        </a>

        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav ms-auto align-items-lg-center gap-3">
            <li><span class="badge role-badge"><i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?></span></li>
            <li><a class="nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php"><i class="bi bi-people me-1"></i> المستخدمون</a></li>
            <li><a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-1"></i> خروج</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </div>

  <!-- Sidebar -->
  <div class="offcanvas offcanvas-start" id="sidebarMenu">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-orange">القائمة</h5>
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
      <aside class="col-lg-2 col-md-3 border-end min-vh-100 d-none d-md-block">
        <div class="p-3">
          <div class="text-muted small mb-2">القائمة</div>
          <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
          <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
          <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
          <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
          <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
          <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
          <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
        </div>
      </aside>

      <main class="col-12 col-md-9 col-lg-10">
        <?php if($m = flash('msg')): ?>
          <div class="alert alert-info"><?= esc($m) ?></div>
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
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
