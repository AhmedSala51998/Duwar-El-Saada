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

  <!-- الخطوط -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap RTL -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

  <!-- أيقونات -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Theme CSS -->
  <link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f5f7fa;
      color: #333;
    }

    /* Loader */
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
    .circle{
      position:relative;
      width:140px;
      height:140px;
      border-radius:50%;
      border:4px solid rgba(255,127,50,0.2);
      display:flex;
      justify-content:center;
      align-items:center;
      animation:spin 3s linear infinite;
    }
    .loader-text{
      color:#ff6a00;
      font-size:20px;
      font-weight:bold;
      text-align:center;
      text-shadow:0 0 8px rgba(255,106,0,0.6);
      animation:pulse 2s ease-in-out infinite;
      z-index:2;
    }
    .pulse-dot{
      position:absolute;
      width:8px; height:8px;
      border-radius:50%;
      background:#ff6a00;
      opacity:0.8;
      transform:scale(0);
      animation:dotPulse 1.5s infinite ease-in-out;
    }
    .pulse-dot:nth-child(1){top:0; left:50%; animation-delay:0s;}
    .pulse-dot:nth-child(2){top:15%; right:0; animation-delay:0.1s;}
    .pulse-dot:nth-child(3){bottom:15%; right:0; animation-delay:0.2s;}
    .pulse-dot:nth-child(4){bottom:0; left:50%; animation-delay:0.3s;}
    .pulse-dot:nth-child(5){bottom:15%; left:0; animation-delay:0.4s;}
    .pulse-dot:nth-child(6){top:15%; left:0; animation-delay:0.5s;}
    @keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
    @keyframes pulse{0%,100%{transform:scale(1);}50%{transform:scale(1.1);}}
    @keyframes dotPulse{0%{transform:scale(0); opacity:0;}50%{transform:scale(1); opacity:1;}100%{transform:scale(0); opacity:0;}}
    body.loading > *:not(.loader){opacity:0; pointer-events:none;}

    /* Navbar */
    .custom-navbar {
      background: #fff;
      backdrop-filter: blur(8px);
      border-bottom: 1px solid rgba(0,0,0,0.05);
      padding: 0.3rem 1rem;
    }
    .custom-navbar .navbar-brand img {height:50px; width:50px;}
    .custom-navbar .navbar-brand span {font-size:1.2rem; font-weight:700;}
    .nav-link.active, .sidebar-link.active {
      background-color:#ff6a00;
      color:#fff !important;
      border-radius:6px;
    }

    /* Sidebar */
    aside{
      background:#fff;
      border-right:1px solid rgba(0,0,0,0.05);
      min-height:100vh;
      padding:1rem;
    }
    .sidebar-link{
      display:block;
      padding:.6rem 1rem;
      margin-bottom:.4rem;
      color:#555;
      border-radius:8px;
      transition:.2s;
    }
    .sidebar-link:hover{background:rgba(255,106,0,0.08); color:#ff6a00;}
    .role-badge{background:#fff3e6;color:#ff6a00;font-weight:600;border-radius:50px;padding:.4rem .8rem; box-shadow:0 2px 6px rgba(255,106,0,.2);}
    .btn-logout{background:linear-gradient(135deg,#ff6a00,#ff944d); color:#fff; font-weight:600; padding:.5rem 1rem; border-radius:50px; transition:.3s;}
    .btn-logout:hover{transform:translateY(-2px); box-shadow:0 5px 12px rgba(255,106,0,.4);}
  </style>
</head>
<body class="loading">
<div class="loader">
  <div class="circle">
    <div class="loader-text">جارٍ التحميل</div>
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
    <a class="navbar-brand d-flex align-items-center gap-2 text-orange" href="<?= BASE_URL ?>/home.php">
      <img src="<?= BASE_URL ?>/assets/logo.png" alt="logo" class="rounded shadow-sm">
      <span><?= esc(APP_NAME) ?></span>
    </a>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-3">
        <li class="nav-item"><span class="badge role-badge"><i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?></span></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php"><i class="bi bi-people me-1"></i> المستخدمون</a></li>
        <li class="nav-item"><a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-1"></i> خروج</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header"><h5 class="offcanvas-title">القائمة</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
  <div class="offcanvas-body">
    <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
    <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> المشتريات</a>
    <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
    <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
    <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
    <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
    <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <aside class="col-lg-2 col-md-3 d-none d-md-block border-end">
      <div class="p-3">
        <div class="text-muted small mb-2">القائمة</div>
        <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
        <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> المشتريات</a>
        <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
        <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
        <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
        <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
        <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
      </div>
    </aside>

    <main class="col-12 col-md-9 col-lg-10 p-4">
      <?php if($m = flash('msg')): ?>
        <div class="alert alert-success"><?= esc($m) ?></div>
      <?php endif; ?>
      <!-- المحتوى هنا -->
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.addEventListener('load', () => {
    document.querySelector('.loader').classList.add('hidden');
    document.getElementById('page-wrapper').style.opacity='1';
  });
</script>
</body>
</html>
