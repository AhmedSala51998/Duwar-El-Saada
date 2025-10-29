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

  <!-- خطوط -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f8f9fb;
      color: #333;
    }

    /* ===== Loader ===== */
    .loader {
      position: fixed;
      inset: 0;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      z-index: 9999;
      transition: opacity .8s ease, visibility .8s ease;
    }
    .loader.hidden { opacity: 0; visibility: hidden; }

    .circle {
      width: 130px; height: 130px;
      border-radius: 50%;
      border: 4px solid rgba(255, 122, 0, 0.2);
      display: flex; align-items: center; justify-content: center;
      animation: spin 3s linear infinite;
      position: relative;
    }
    .loader-text {
      font-weight: bold;
      color: #ff7f32;
      font-size: 1.4rem;
      text-shadow: 0 0 10px rgba(255,127,50,0.7);
      animation: pulse 2s infinite;
    }
    .pulse-dot {
      position: absolute;
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #ff7f32;
      opacity: 0.7;
      transform: scale(0);
      animation: dotPulse 1.5s infinite ease-in-out;
    }
    .pulse-dot:nth-child(1){top:0; left:50%; animation-delay:0s;}
    .pulse-dot:nth-child(2){top:15%; right:0; animation-delay:0.1s;}
    .pulse-dot:nth-child(3){bottom:15%; right:0; animation-delay:0.2s;}
    .pulse-dot:nth-child(4){bottom:0; left:50%; animation-delay:0.3s;}
    .pulse-dot:nth-child(5){bottom:15%; left:0; animation-delay:0.4s;}
    .pulse-dot:nth-child(6){top:15%; left:0; animation-delay:0.5s;}

    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes pulse {
      0%,100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.1); opacity: 0.8; }
    }
    @keyframes dotPulse {
      0%,100% { transform: scale(0); opacity: 0; }
      50% { transform: scale(1); opacity: 1; }
    }

    /* ===== Navbar ===== */
    .custom-navbar {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0,0,0,0.05);
      box-shadow: 0 2px 6px rgba(0,0,0,0.06);
      padding: .6rem 1rem;
    }

    .navbar-brand img {
      width: 55px; height: 55px;
    }

    .navbar .nav-link {
      font-weight: 500;
      color: #555 !important;
      padding: .5rem 1rem;
      border-radius: 10px;
      transition: all .2s ease;
    }
    .navbar .nav-link:hover {
      background: rgba(255,106,0,.1);
      color: #ff6a00 !important;
    }
    .navbar .nav-link.active {
      background: rgba(255,106,0,.15);
      color: #ff6a00 !important;
      font-weight: 600;
    }

    /* ===== Sidebar ===== */
    aside {
      background: #fff;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    .sidebar-link {
      display: block;
      color: #555;
      text-decoration: none;
      padding: .65rem 1rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all .2s;
    }
    .sidebar-link:hover {
      background: rgba(255,106,0,.08);
      color: #ff6a00;
    }
    .sidebar-link.active {
      background: linear-gradient(135deg,#ff7f32,#ff9b4d);
      color: #fff !important;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(255,106,0,.3);
    }

    /* ===== Role Badge ===== */
    .role-badge {
      background: #fff3e6;
      color: #ff6a00;
      font-weight: 600;
      border-radius: 50px;
      padding: .4rem 1rem;
      box-shadow: 0 2px 4px rgba(255,106,0,.2);
    }

    /* ===== Logout Button ===== */
    .btn-logout {
      background: linear-gradient(135deg,#ff6a00,#ff944d);
      color: #fff;
      font-weight: 600;
      padding: .5rem 1.2rem;
      border-radius: 50px;
      transition: all .3s ease;
      box-shadow: 0 3px 10px rgba(255,106,0,.3);
    }
    .btn-logout:hover {
      background: linear-gradient(135deg,#e65a00,#ff7a1f);
      transform: translateY(-2px);
      box-shadow: 0 5px 14px rgba(255,106,0,.4);
      color: #fff !important;
    }

    /* ===== Main ===== */
    main {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,.05);
      margin-top: 1rem;
      padding: 2rem;
      transition: opacity .5s ease;
    }

    /* تحسين التباعد في الموبايل */
    @media (max-width: 768px) {
      .navbar-brand span { font-size: 1.1rem; }
      main { padding: 1.2rem; }
    }
  </style>
</head>

<body>
  <!-- Loader -->
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
        <button class="btn d-md-none me-2 text-orange fs-3 border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
          <i class="bi bi-list"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-orange" href="<?= BASE_URL ?>/home.php">
          <img src="<?= BASE_URL ?>/assets/logo.png" alt="logo" class="rounded shadow-sm">
          <span><?= esc(APP_NAME) ?></span>
        </a>

        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav ms-auto align-items-lg-center gap-3">
            <li class="nav-item">
              <span class="badge role-badge">
                <i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?>
              </span>
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

  <!-- Sidebar -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">القائمة</h5>
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
      <aside class="col-lg-2 col-md-3 border-end min-vh-100 d-none d-md-block p-3">
        <div class="text-muted small mb-2">القائمة</div>
        <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
        <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
        <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
        <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
        <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
        <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
        <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
      </aside>

      <main class="col-12 col-md-9 col-lg-10">
        <?php if($m = flash('msg')): ?>
          <div class="alert alert-success"><?= esc($m) ?></div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <script>
    window.addEventListener('load', () => {
      document.querySelector('.loader').classList.add('hidden');
      document.getElementById('page-wrapper').style.opacity = '1';
    });
  </script>
</body>
</html>
