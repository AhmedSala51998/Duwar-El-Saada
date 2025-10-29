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

  <!-- خطوط -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Scheherazade+New:wght@700&display=swap" rel="stylesheet">

  <!-- Bootstrap RTL -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

  <!-- أيقونات -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- ملف الثيم إن وُجد -->
  <link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">

  <style>
    :root{
      --bg: #f4f6f9;
      --card: #ffffff;
      --muted: #6c757d;
      --text: #222;
      --accent: #ff6a00;
      --accent-600: #e65a00;
      --glass: rgba(255,255,255,0.65);
      --navy: #0b1220;
    }

    /* Dark mode vars */
    [data-theme="dark"]{
      --bg: #0f1720;
      --card: #0b1220;
      --muted: #98a0ad;
      --text: #e6eef6;
      --accent: #ff8a3d;
      --accent-600: #ff7a1f;
      --glass: rgba(255,255,255,0.03);
    }

    html,body {
      height:100%;
      background: linear-gradient(180deg, var(--bg) 0%, rgba(255,255,255,0.02) 100%);
      font-family: "Cairo", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      color: var(--text);
      transition: background .35s ease, color .35s ease;
    }

    /* Loader */
    .loader{
      position:fixed;
      inset:0;
      display:flex;
      justify-content:center;
      align-items:center;
      z-index:9999;
      background: var(--card);
      transition:opacity .6s ease, visibility .6s ease;
    }
    .loader.hidden{opacity:0; visibility:hidden; pointer-events:none;}

    .loader-inner{
      display:flex;
      gap:18px;
      align-items:center;
      flex-direction:column;
    }

    .spin-ring{
      width:120px;
      height:120px;
      border-radius:50%;
      background: conic-gradient(var(--accent) 0 30%, rgba(0,0,0,0.06) 30% 100%);
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow: 0 8px 30px rgba(0,0,0,0.08);
      animation:spin 2.6s linear infinite;
      position:relative;
      overflow:hidden;
    }

    .spin-ring::after{
      content:"";
      position:absolute;
      inset:8px;
      border-radius:50%;
      background:var(--card);
      box-shadow: inset 0 1px 0 rgba(0,0,0,0.02);
    }

    .loader-text{
      position:relative;
      z-index:2;
      font-weight:700;
      color:var(--accent);
      letter-spacing:0.25px;
      text-shadow: 0 4px 18px rgba(255,106,0,0.12);
      font-size:18px;
      margin-top:8px;
    }

    @keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}

    /* Navbar */
    .custom-navbar {
      background: linear-gradient(180deg, var(--glass), rgba(255,255,255,0));
      backdrop-filter: blur(8px);
      box-shadow: 0 6px 20px rgba(11,18,32,0.04);
      border-bottom: 1px solid rgba(0,0,0,0.04);
      padding: .5rem 1rem;
    }

    .navbar-brand {
      display:flex;
      align-items:center;
      gap:.6rem;
    }

    .navbar-brand img {
      height:56px;
      width:56px;
      object-fit:cover;
      border-radius:12px;
      box-shadow: 0 8px 20px rgba(11,18,32,0.06);
    }

    .app-title {
      font-family: 'Scheherazade New', serif;
      font-weight:700;
      font-size:1.1rem;
      color:var(--text);
      line-height:1;
    }

    /* role badge */
    .role-badge {
      background: rgba(255,106,0,0.08);
      color: var(--accent-600);
      padding: .45rem .9rem;
      border-radius:999px;
      font-weight:700;
      box-shadow: 0 6px 18px rgba(255,106,0,0.06);
    }

    /* logout */
    .btn-logout {
      background: linear-gradient(135deg,var(--accent),#ff944d);
      color: #fff;
      font-weight:700;
      padding: .5rem .95rem;
      border-radius:999px;
      border: none;
      box-shadow: 0 8px 20px rgba(255,106,0,0.12);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .btn-logout:active{ transform: translateY(1px); }

    /* Dark toggle */
    .theme-toggle {
      background: transparent;
      border: 1px solid rgba(0,0,0,0.06);
      padding: .45rem .6rem;
      border-radius:10px;
      display:flex;
      gap:.6rem;
      align-items:center;
      cursor:pointer;
    }
    [data-theme="dark"] .theme-toggle{
      border: 1px solid rgba(255,255,255,0.08);
      color:var(--text);
    }

    /* Sidebar */
    aside {
      background: transparent;
    }

    .sidebar {
      position: sticky;
      top:0;
      padding:1rem;
      border-radius:12px;
      margin-top:8px;
      transition: all .25s ease;
    }

    .sidebar .sidebar-link {
      display:flex;
      align-items:center;
      gap:.75rem;
      padding:.65rem .8rem;
      color:var(--muted);
      border-radius:10px;
      transition: all .18s ease;
      font-weight:600;
    }
    .sidebar .sidebar-link i { font-size:1.05rem; color:var(--accent); }

    .sidebar .sidebar-link:hover{
      background: rgba(255,106,0,0.06);
      color:var(--text);
      transform: translateX(-6px);
      box-shadow: 0 8px 18px rgba(11,18,32,0.04);
    }

    .sidebar .sidebar-link.active{
      background: linear-gradient(90deg, rgba(255,106,0,0.12), rgba(255,106,0,0.06));
      color: var(--accent-600);
      box-shadow: inset 0 -3px 0 rgba(255,106,0,0.02);
    }

    /* responsive tweaks */
    @media (max-width: 991px){
      .sidebar { background: transparent; padding: .6rem; }
      .navbar-brand .app-title { display:none; }
    }

    /* simple fade/slide animations for page content */
    .fade-slide {
      transform: translateY(10px);
      opacity:0;
      transition: transform .45s cubic-bezier(.2,.9,.2,1), opacity .45s ease;
    }
    .fade-slide.visible { transform: translateY(0); opacity:1; }

    /* small helpers */
    .text-orange { color: var(--accent) !important; }
    .muted-sm { color: var(--muted); font-size:.9rem; }

  </style>
</head>
<body>

  <!-- Loader -->
  <div class="loader" role="status" aria-label="جارٍ التحميل">
    <div class="loader-inner">
      <div class="spin-ring" aria-hidden="true"></div>
      <div class="loader-text">دوار السعادة</div>
    </div>
  </div>

  <div id="page-wrapper" style="opacity:0; transition:opacity .6s ease;">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg sticky-top custom-navbar" aria-label="الشريط العلوي">
      <div class="container-fluid">

        <!-- زر القائمة للموبايل -->
        <button class="btn d-md-none me-2 text-orange fs-3 border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-label="فتح القائمة">
          <i class="bi bi-list"></i>
        </button>

        <!-- اللوجو والاسم -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/home.php" aria-label="الصفحة الرئيسية">
          <img src="<?= BASE_URL ?>/assets/logo.png" alt="logo">
          <div class="d-flex flex-column">
            <span class="app-title"><?= esc(APP_NAME) ?></span>
            <small class="muted-sm"><?= esc(current_user()) ?></small>
          </div>
        </a>

        <!-- زرار التوغل للنافبار -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="تبديل شريط التنقل">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- عناصر النافبار -->
        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav ms-auto align-items-lg-center gap-3">

            <!-- تبديل الوضع -->
            <li class="nav-item d-flex align-items-center">
              <button id="themeToggle" class="theme-toggle" aria-pressed="false" title="تبديل الوضع">
                <i id="themeIcon" class="bi bi-sun-fill"></i>
                <small class="muted-sm d-none d-md-inline">الوضع</small>
              </button>
            </li>

            <!-- الدور -->
            <li class="nav-item">
              <span class="role-badge" aria-hidden="false" title="الدور الحالي">
                <i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?>
              </span>
            </li>

            <!-- المستخدمون -->
            <li class="nav-item">
              <a class="nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php" aria-current="<?= $current_page=='users.php'?'page':'' ?>">
                <i class="bi bi-people me-1"></i> المستخدمون
              </a>
            </li>

            <!-- خروج -->
            <li class="nav-item">
              <a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php" role="button">
                <i class="bi bi-box-arrow-right me-1"></i> خروج
              </a>
            </li>

          </ul>
        </div>
      </div>
    </nav>

    <!-- Offcanvas Sidebar (mobile) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">القائمة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
      </div>
      <div class="offcanvas-body">
        <nav class="d-flex flex-column">
          <a class="sidebar-link mb-2 <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
          <a class="sidebar-link mb-2 <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
          <a class="sidebar-link mb-2 <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
          <a class="sidebar-link mb-2 <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies.php"><i class="bi bi-wallet2"></i> العهد</a>
          <a class="sidebar-link mb-2 <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
          <a class="sidebar-link mb-2 <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
          <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
        </nav>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row g-0">
        <!-- Sidebar على الديسكتوب -->
        <aside class="col-lg-2 col-md-3 d-none d-md-block">
          <div class="sidebar fade-slide p-2">
            <div class="text-muted small mb-3">القائمة</div>

            <nav class="d-flex flex-column">
              <a class="sidebar-link mb-2 <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
              <a class="sidebar-link mb-2 <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
              <a class="sidebar-link mb-2 <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
              <a class="sidebar-link mb-2 <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies.php"><i class="bi bi-wallet2"></i> العهد</a>
              <a class="sidebar-link mb-2 <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
              <a class="sidebar-link mb-2 <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
              <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
            </nav>
          </div>
        </aside>

        <!-- Main content column يبدأ في ملف آخر -->
        <main class="col-12 col-md-9 col-lg-10 p-4">
          <?php if($m = flash('msg')): ?>
            <div class="alert alert-info fade-slide"><?= esc($m) ?></div>
          <?php endif; ?>

<!-- سيتم إغلاق العناصر والـ footer في ملف footer.php -->
<script>
  // DOM Ready
  document.addEventListener('DOMContentLoaded', () => {
    const loader = document.querySelector('.loader');
    const page = document.getElementById('page-wrapper') || document.body;
    const fadeEls = document.querySelectorAll('.fade-slide');

    // Theme handling (persist)
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const savedTheme = localStorage.getItem('app-theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    function applyTheme(t){
      if(t === 'dark'){
        document.documentElement.setAttribute('data-theme', 'dark');
        themeIcon.className = 'bi bi-moon-fill';
        themeToggle.setAttribute('aria-pressed', 'true');
      } else {
        document.documentElement.removeAttribute('data-theme');
        themeIcon.className = 'bi bi-sun-fill';
        themeToggle.setAttribute('aria-pressed', 'false');
      }
      localStorage.setItem('app-theme', t);
    }
    applyTheme(savedTheme);

    if(themeToggle){
      themeToggle.addEventListener('click', () => {
        const current = localStorage.getItem('app-theme') === 'dark' ? 'dark' : 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
      });
    }

    // On window load (when all assets loaded) hide loader and reveal page
    window.addEventListener('load', () => {
      if(loader) loader.classList.add('hidden');
      if(page) page.style.opacity = '1';

      // reveal fade-slide elements with small stagger
      fadeEls.forEach((el, idx) => {
        setTimeout(() => el.classList.add('visible'), 80 * idx);
      });

      // show bootstrap toasts if any
      const toastEl = document.getElementById('liveToast');
      if(toastEl){
        const toast = new bootstrap.Toast(toastEl, { delay: 2800 });
        toast.show();
      }
    });

    // Accessibility: close offcanvas with ESC (Bootstrap does, but safe)
    document.addEventListener('keydown', (e) => {
      if(e.key === 'Escape'){
        const off = document.querySelector('.offcanvas.show');
        if(off){
          const bsOff = bootstrap.Offcanvas.getInstance(off);
          if(bsOff) bsOff.hide();
        }
      }
    });
  });
</script>
