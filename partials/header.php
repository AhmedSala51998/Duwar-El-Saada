<style>
  /* Navbar */
  .custom-navbar {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0,0,0,0.08);
    padding: 0.6rem 1rem;
    transition: all 0.3s ease;
    z-index: 1030;
  }

  .navbar-brand img {
    transition: transform 0.3s;
  }
  .navbar-brand:hover img {
    transform: scale(1.05);
  }

  .role-badge {
    background: #fff3e6;
    color: #ff6a00;
    font-weight: 600;
    border-radius: 50px;
    padding: 0.4rem 0.9rem;
    box-shadow: 0 2px 6px rgba(255,106,0,0.2);
    font-size: 0.9rem;
  }

  .btn-logout {
    background: linear-gradient(135deg,#ff6a00,#ff944d);
    color: #fff;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(255,106,0,0.3);
    transition: all 0.3s ease;
  }
  .btn-logout:hover {
    background: linear-gradient(135deg,#e65a00,#ff7a1f);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(255,106,0,0.4);
    color: #fff !important;
  }

  /* Sidebar */
  .sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    color: #555;
    transition: all 0.2s ease;
  }
  .sidebar-link:hover {
    background: rgba(255,106,0,0.08);
    color: #ff6a00;
  }
  .sidebar-link.active {
    background: rgba(255,106,0,0.15);
    color: #ff6a00;
    font-weight: 600;
  }

  /* Offcanvas for mobile */
  .offcanvas .sidebar-link {
    margin-bottom: 0.5rem;
  }

  @media (max-width: 768px) {
    .navbar .role-badge {
      font-size: 0.8rem;
      padding: 0.3rem 0.7rem;
    }
    .btn-logout {
      padding: 0.4rem 1rem;
      font-size: 0.9rem;
    }
  }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top custom-navbar">
  <div class="container-fluid">
    <!-- Mobile Menu Button -->
    <button class="btn d-md-none me-2 text-orange fs-3 border-0" 
            data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
      <i class="bi bi-list"></i>
    </button>

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-orange" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/logo.svg" width="40" height="40" alt="logo">
      <span class="fs-5"><?= esc(APP_NAME) ?></span>
    </a>

    <!-- Navbar items -->
    <div class="collapse navbar-collapse justify-content-end" id="nav">
      <ul class="navbar-nav align-items-center gap-2">
        <li class="nav-item">
          <span class="badge role-badge"><i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?></span>
        </li>
        <li class="nav-item d-none d-lg-block">
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

<!-- Offcanvas Sidebar for Mobile -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">القائمة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-2">
    <a class="sidebar-link <?= $current_page=='index.php'?'active':'' ?>" href="<?= BASE_URL ?>/index.php"><i class="bi bi-house"></i> الرئيسية</a>
    <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> المشتريات</a>
    <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
    <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies.php"><i class="bi bi-wallet2"></i> العهد</a>
    <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
    <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
    <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
  </div>
</div>
