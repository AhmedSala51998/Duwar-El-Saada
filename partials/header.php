<?php require __DIR__.'/../config/config.php'; require_auth(); ?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
  <div class="container-fluid">
    <!-- زر القائمة للموبايل -->
    <button class="btn btn-outline-secondary d-md-none me-2" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
      <i class="bi bi-list"></i>
    </button>

    <!-- لوجو -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/logo.svg" width="36" height="36" alt="logo">
      <span class="fw-bold"><?= esc(APP_NAME) ?></span>
    </a>

    <!-- زرار القايمة في الديسكتوب -->
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item me-3">
          <span class="badge bg-secondary-subtle text-dark">دور: <?= esc(current_role()) ?></span>
        </li>
        <li class="nav-item me-2">
          <a class="nav-link" href="<?= BASE_URL ?>/users.php"><i class="bi bi-people"></i> المستخدمون</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-orange" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> خروج</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- القائمة الجانبية في الموبايل (Offcanvas) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">القائمة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/index.php"><i class="bi bi-house"></i> الرئيسية</a>
    <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
    <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
    <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/assets.php"><i class="bi bi-building"></i> العُهد</a>
    <a class="sidebar-link d-block" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar في الديسكتوب -->
    <aside class="col-lg-2 col-md-3 border-end min-vh-100 d-none d-md-block">
      <div class="p-3">
        <div class="text-muted small mb-2">القائمة</div>
        <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/index.php"><i class="bi bi-house"></i> الرئيسية</a>
        <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
        <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
        <a class="sidebar-link d-block mb-2" href="<?= BASE_URL ?>/assets.php"><i class="bi bi-building"></i> العُهد</a>
        <a class="sidebar-link d-block" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
      </div>
    </aside>

    <!-- المحتوى -->
    <main class="col-12 col-md-9 col-lg-10 p-4">
      <?php if($m = flash('msg')): ?>
        <div class="flash mb-3"><?= esc($m) ?></div>
      <?php endif; ?>
