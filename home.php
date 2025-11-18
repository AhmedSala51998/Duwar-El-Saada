<?php require __DIR__.'/partials/header.php'; ?>

<style>
/* ==========================================================
   ✨ ULTRA PREMIUM NEO-GLASS DASHBOARD — كامل وجاهز
   ========================================================== */

body {
  background: linear-gradient(135deg, #f7f8fc, #eef1f8) !important;
  font-family: 'Cairo', sans-serif;
  color: #222;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}

/* ===== container spacing ===== */
.container {
  max-width: 1200px;
  margin: 26px auto;
  padding: 0 16px;
}

/* ============================= */
/* Hero Card - Neo Glass         */
/* ============================= */
.hero-card {
  position: relative;
  background: linear-gradient(90deg, rgba(255,255,255,0.85), rgba(255,255,255,0.75));
  backdrop-filter: blur(14px);
  border-radius: 32px;
  padding: 56px 40px 40px 40px;
  border: 1px solid rgba(255,255,255,0.6);
  box-shadow: 0 20px 60px rgba(10,20,30,0.08);
  overflow: visible; /* important to show floating logo */
}

/* decorative blobs */
.hero-card::before,
.hero-card::after {
  content:'';
  position: absolute;
  width: 240px;
  height: 240px;
  border-radius: 50%;
  filter: blur(36px);
  opacity: 0.55;
  pointer-events: none;
}
.hero-card::before { top: -40px; right: -20px; background: radial-gradient(circle, rgba(255,170,90,0.6), rgba(255,106,0,0)); }
.hero-card::after  { bottom: -60px; left: -20px; background: radial-gradient(circle, rgba(112,162,255,0.18), rgba(255,106,0,0)); }

.hero-title {
  font-size: 2.6rem;
  font-weight: 900;
  color: #1f2933;
  text-align: right;
  margin: 0 0 8px 0;
}

.hero-sub {
  font-size: 1.05rem;
  color: #3a3f45;
  opacity: 0.9;
  text-align: right;
  margin: 0;
}

/* ============================= */
/* Floating Logo (not cropped)   */
/* ============================= */
.floating-logo {
  position: absolute;
  top: -50px;     /* show above card */
  left: 26px;
  width: 110px;
  height: 110px;
  border-radius: 50%;
  background: rgba(255,255,255,0.88);
  border: 6px solid rgba(255,166,66,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 14px 40px rgba(255,106,0,0.18);
  z-index: 40;
  animation: floatY 3.2s ease-in-out infinite;
}
.floating-logo img { width: 74%; height: 74%; object-fit: contain; }

@keyframes floatY {
  0% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
  100% { transform: translateY(0); }
}

/* ============================= */
/* Stat cards (glass + neumorph) */
/* ============================= */
.stat-card {
  background: linear-gradient(180deg, rgba(255,255,255,0.85), rgba(250,250,250,0.9));
  border-radius: 22px;
  padding: 22px;
  text-align: center;
  border: 1px solid rgba(255,255,255,0.6);
  box-shadow: 8px 10px 30px rgba(14,20,30,0.06), -6px -6px 18px rgba(255,255,255,0.9);
  transition: transform .28s ease, box-shadow .28s ease;
  height: 140px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}
.stat-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 14px 18px 46px rgba(10,20,30,0.10);
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  margin: 0 auto;
  background: linear-gradient(145deg,#fff,#eee);
  color: #ff6a00;
  box-shadow: inset 4px 4px 12px rgba(0,0,0,0.06), inset -4px -4px 12px rgba(255,255,255,0.9);
}
.stat-title { font-size: .98rem; color:#666; font-weight:600; }
.stat-value { font-size: 1.9rem; font-weight:900; color:#222; }

/* ============================= */
/* Chart cards - fixed height    */
/* ============================= */
.chart-card {
  position: relative;
  padding: 22px;
  border-radius: 26px;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(255,255,255,0.6);
  box-shadow: 0 12px 40px rgba(10,20,30,0.07);
  height: 360px;
  overflow: hidden;
}
.chart-card h5 {
  margin: 0 0 10px 0;
  font-size: 1.15rem;
  font-weight:800;
  color:#222;
}

/* inner chart wrapper to control canvas sizing */
.chart-inner {
  position: absolute;
  left: 22px;
  right: 22px;
  bottom: 18px;
  top: 62px; /* leaves room for h5 */
}
.chart-inner canvas {
  width: 100% !important;
  height: 100% !important;
  display:block;
}

/* small utilities */
.text-purple { color: #6f42c1 !important; }

/* responsive tweaks */
@media (max-width: 991px) {
  .hero-title { font-size: 2.2rem; }
  .hero-sub { font-size: 1rem; }
  .stat-card { height: 130px; }
  .chart-card { height: 320px; }
}
@media (max-width: 576px) {
  .hero-card { padding: 36px 18px 28px; }
  .floating-logo { left: 12px; top: -40px; width: 86px; height: 86px; }
  .hero-title { font-size: 1.6rem; }
  .chart-card { height: 280px; }
}
</style>

<?php
// — نفس الاستعلامات — (لمستندك الأصلي)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pc = (int)$pdo->query("SELECT COUNT(*) c FROM purchases")->fetch()['c'];
$oc = (int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$ac = (int)$pdo->query("SELECT COUNT(*) c FROM assets")->fetch()['c'];
$cc = (int)$pdo->query("SELECT COUNT(*) c FROM custodies")->fetch()['c'];
$expenses_count = (int)$pdo->query("SELECT COUNT(*) c FROM expenses")->fetch()['c'];

$purchasesByMonth = $pdo->query("
  SELECT DATE_FORMAT(op.created_at, '%Y-%m') AS m, COUNT(DISTINCT op.id) AS c
  FROM orders_purchases op
  INNER JOIN purchases p ON op.id = p.order_id
  GROUP BY m ORDER BY m DESC LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

$ordersByMonth = $pdo->query("
  SELECT DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c 
  FROM orders GROUP BY m ORDER BY m DESC LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

$expensesByMonth = $pdo->query("
  SELECT DATE_FORMAT(created_at,'%Y-%m') m, SUM(expense_amount) total 
  FROM expenses GROUP BY m ORDER BY m DESC LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesByMonth = $pdo->query("
  SELECT DATE_FORMAT(taken_at,'%Y-%m') m, COUNT(*) c 
  FROM custodies GROUP BY m ORDER BY m DESC LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

$assetsByPayer = $pdo->query("SELECT payer_name, COUNT(*) c FROM assets GROUP BY payer_name")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="container">

  <!-- HERO -->
  <div class="hero-card mb-5 text-end">
    <div class="floating-logo">
      <img src="<?= BASE_URL ?>/assets/logo.png" alt="Logo" />
    </div>

    <h2 class="hero-title">مرحبًا <?= esc(current_user()) ?> 👋</h2>
    <p class="hero-sub">لوحة تحكم احترافية — تحكم كامل في المشتريات، الأوامر، الأصول، العُهد والمصروفات.</p>
  </div>

  <!-- STATS -->
  <div class="row g-4 mb-4">
    <?php
      $cards = [
        ["الأصناف", $pc, "bi-bag", "text-warning", "purchases.php"],
        ["أوامر التشغيل", $oc, "bi-gear", "text-primary", "orders.php"],
        ["الأصول", $ac, "bi-building", "text-success", "assetes.php"],
        ["العهد", $cc, "bi-wallet2", "text-dark", "custodies.php"],
        ["المصروفات", $expenses_count, "bi-cash-stack", "text-secondary", "expenses.php"],
        ["التقارير", 6, "bi-graph-up-arrow", "text-purple", "reports.php"],
      ];
      foreach ($cards as $c): ?>
        <div class="col-6 col-md-4 col-lg-2">
          <a href="<?= $c[4] ?>" class="text-decoration-none">
            <div class="stat-card">
              <div class="stat-icon <?= $c[3] ?>"><i class="bi <?= $c[2] ?>"></i></div>
              <div class="stat-title"><?= $c[0] ?></div>
              <div class="stat-value"><?= $c[1] ?></div>
            </div>
          </a>
        </div>
    <?php endforeach; ?>
  </div>

  <hr class="my-5">

  <!-- CHARTS ROW -->
  <div class="row g-4">

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-bag text-warning me-1"></i> المشتريات (آخر 6 شهور)</h5>
        <div class="chart-inner"><canvas id="purchasesChart"></canvas></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-gear text-primary me-1"></i> أوامر التشغيل</h5>
        <div class="chart-inner"><canvas id="ordersChart"></canvas></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-wallet2 text-success me-1"></i> العُهد (آخر 6 شهور)</h5>
        <div class="chart-inner"><canvas id="custodiesChart"></canvas></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-cash-stack text-secondary me-1"></i> المصروفات حسب الشهر</h5>
        <div class="chart-inner"><canvas id="expensesChart"></canvas></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-building text-success me-1"></i> الأصول حسب الدافع</h5>
        <div class="chart-inner"><canvas id="assetsChart"></canvas></div>
      </div>
    </div>

  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// تحويل بيانات PHP إلى JS
const purchasesLabels = <?= json_encode(array_keys($purchasesByMonth)) ?> || [];
const purchasesData   = <?= json_encode(array_values($purchasesByMonth)) ?> || [];

const ordersLabels = <?= json_encode(array_keys($ordersByMonth)) ?> || [];
const ordersData   = <?= json_encode(array_values($ordersByMonth)) ?> || [];

const custodiesLabels = <?= json_encode(array_keys($custodiesByMonth)) ?> || [];
const custodiesData   = <?= json_encode(array_values($custodiesByMonth)) ?> || [];

const expensesLabels = <?= json_encode(array_keys($expensesByMonth)) ?> || [];
const expensesData   = <?= json_encode(array_values($expensesByMonth)) ?> || [];

const assetsLabels = <?= json_encode(array_keys($assetsByPayer)) ?> || [];
const assetsData   = <?= json_encode(array_values($assetsByPayer)) ?> || [];

/* Helper to create chart with sensible defaults */
function createChart(ctx, cfg) {
  return new Chart(ctx, Object.assign({
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false, position: 'bottom' },
        tooltip: { mode: 'index', intersect: false }
      },
      layout: { padding: 6 },
      responsive: true
    }
  }, cfg));
}

/* Purchases - bar */
createChart(document.getElementById('purchasesChart'), {
  type: 'bar',
  data: {
    labels: purchasesLabels,
    datasets: [{
      label: 'عدد المشتريات',
      data: purchasesData,
      backgroundColor: 'rgba(255,106,0,0.92)',
      borderRadius: 8,
      maxBarThickness: 32
    }]
  },
  options: {
    scales: {
      x: { grid: { display:false }, ticks: { maxRotation: 0 } },
      y: { beginAtZero: true, grid: { color:'rgba(0,0,0,0.04)' } }
    }
  }
});

/* Orders - line */
createChart(document.getElementById('ordersChart'), {
  type: 'line',
  data: {
    labels: ordersLabels,
    datasets: [{
      label: 'عدد الأوامر',
      data: ordersData,
      borderColor: '#007bff',
      backgroundColor: 'rgba(0,123,255,0.14)',
      tension: 0.36,
      fill: true,
      pointRadius: 4,
      pointHoverRadius: 6
    }]
  },
  options: {
    scales: {
      x: { grid: { display:false } },
      y: { beginAtZero:true, grid: { color:'rgba(0,0,0,0.04)' } }
    }
  }
});

/* Custodies - bar */
createChart(document.getElementById('custodiesChart'), {
  type: 'bar',
  data: {
    labels: custodiesLabels,
    datasets: [{
      label: 'عدد العهد',
      data: custodiesData,
      backgroundColor: 'rgba(40,167,69,0.9)',
      borderRadius: 8,
      maxBarThickness: 32
    }]
  },
  options: {
    scales: { x: { grid:{display:false} }, y: { beginAtZero:true } }
  }
});

/* Expenses - bar */
createChart(document.getElementById('expensesChart'), {
  type: 'bar',
  data: {
    labels: expensesLabels,
    datasets: [{
      label: 'المصروفات',
      data: expensesData,
      backgroundColor: 'rgba(108,117,125,0.9)',
      borderRadius: 8,
      maxBarThickness: 32
    }]
  },
  options: {
    scales: { x: { grid:{display:false} }, y: { beginAtZero:true } }
  }
});

/* Assets - doughnut */
createChart(document.getElementById('assetsChart'), {
  type: 'doughnut',
  data: {
    labels: assetsLabels,
    datasets: [{
      data: assetsData,
      backgroundColor: ['#ff6a00','#007bff','#28a745','#ffc107','#dc3545']
    }]
  },
  options: {
    plugins: { legend: { display: true, position: 'bottom' } }
  }
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
