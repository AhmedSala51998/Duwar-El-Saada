<?php require __DIR__.'/partials/header.php'; ?>

<style>

  .dashboard-header {
    background: linear-gradient(135deg, #ff6a00, #ffb478);
    border-radius: 20px;
    color: #fff;
    padding: 30px;
    box-shadow: 0 6px 20px rgba(255, 106, 0, 0.4);
  }

  .stat-card {
    border: none;
    border-radius: 16px;
    background: #fff;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    position: relative;
    overflow: hidden;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px rgba(255, 106, 0, 0.3);
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
    margin: 0 auto 10px;
  }

  .stat-title {
    font-size: 0.9rem;
    color: #888;
  }

  .stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
  }

  .chart-card {
    border-radius: 16px;
    background: #fff;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }

  .chart-card:hover {
    box-shadow: 0 4px 25px rgba(255,106,0,0.15);
  }

  h5 {
    color: #333;
    font-weight: 600;
  }


.dashboard-card {
  background: linear-gradient(90deg, #ff6a00, #ff944d);
  border-radius: 25px;
  color: white;
  position: relative;
  overflow: visible;
  margin-top: 20px !important; /* مسافة من فوق علشان الصورة تبان */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* الأيقونة */
.chef-icon {
  position: absolute;
  top: -35px; /* نازل شوية علشان يبين */
  left: 20px; /* moved a bit to the right */
  width: 90px;
  height: 90px;
  border-radius: 50%;
  background: #fff;
  border: 5px solid #ff944d;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 0 0 rgba(255, 148, 77, 0.6);
  animation: pulse 2s infinite;
}

.chef-icon img {
  width: 75%;
  height: 75%;
  object-fit: contain;
}

/* تأثير النبض */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 148, 77, 0.6);
  }
  70% {
    box-shadow: 0 0 0 15px rgba(255, 148, 77, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(255, 148, 77, 0);
  }
}


</style>

<?php
// PHP counters and queries (نفس الكود السابق بالضبط)
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

<!--<div class="dashboard-header mb-4">
  <h3 class="fw-bold mb-2">👋 أهلًا <?= esc(current_user()) ?></h3>
  <p class="mb-0 fs-6">أدِر المشتريات، الأوامر، العُهد، والمصروفات بسهولة واحترافية.</p>
</div>-->

<div class="dashboard-card position-relative p-4 mb-4">
  <!-- الأيقونة -->
  <div class="chef-icon">
    <img src="<?= BASE_URL ?>/assets/logo.png" alt="Chef" />
  </div>

  <!-- الهيدر -->
  <div class="dashboard-header text-end">
    <h3 style="text-align:right !important" class="fw-bold mb-2"> أهلًا <?= esc(current_user()) ?> 👋</h3>
  <p style="text-align:right !important" class="mb-0 fs-6">
    أدِر المشتريات، الأوامر، العُهد، المصروفات، والأصول بسهولة واحترافية.
  </p>
</div>
</div>

<div class="row g-4">
  <!-- الكروت الإحصائية -->
  <?php
  $cards = [
    ["الأصناف", $pc, "bi-bag", "text-warning", "purchases.php"],
    ["الأوامر", $oc, "bi-gear", "text-primary", "orders.php"],
    ["الأصول", $ac, "bi-building", "text-success", "assetes.php"],
    ["العهد", $cc, "bi-shield-check", "text-dark", "custodies.php"],
    ["المصروفات", $expenses_count, "bi-cash-stack", "text-secondary", "expenses.php"],
  ];
  foreach ($cards as $c): ?>
    <div class="col-6 col-md-4 col-lg-2">
      <a href="<?= $c[4] ?>" class="text-decoration-none">
        <div class="stat-card text-center p-3">
          <div class="stat-icon <?= $c[3] ?>"><i class="bi <?= $c[2] ?>"></i></div>
          <div class="stat-title"><?= $c[0] ?></div>
          <div class="stat-value"><?= $c[1] ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<hr class="my-5">

<!-- الشارتات -->
<div class="row g-4">
  <div class="col-md-6">
    <div class="chart-card">
      <h5 class="mb-3"><i class="bi bi-bag text-warning me-1"></i> المشتريات (آخر 6 شهور)</h5>
      <canvas id="purchasesChart" height="200"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="chart-card">
      <h5 class="mb-3"><i class="bi bi-gear text-primary me-1"></i> أوامر التشغيل</h5>
      <canvas id="ordersChart" height="200"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="chart-card">
      <h5 class="mb-3"><i class="bi bi-shield-check text-success me-1"></i> العهد (آخر 6 شهور)</h5>
      <canvas id="custodiesChart" height="200"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="chart-card">
      <h5 class="mb-3"><i class="bi bi-cash-stack text-secondary me-1"></i> المصروفات حسب الشهر</h5>
      <canvas id="expensesChart" height="200"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="chart-card">
      <h5 class="mb-3"><i class="bi bi-building text-success me-1"></i> الأصول حسب الدافع</h5>
      <canvas id="assetsChart" height="200"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('purchasesChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($purchasesByMonth)) ?>,
    datasets: [{ 
      label: 'عدد المشتريات', 
      data: <?= json_encode(array_values($purchasesByMonth)) ?>,
      backgroundColor: 'rgba(255, 106, 0, 0.7)',
      borderRadius: 8
    }]
  },
  options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('ordersChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_keys($ordersByMonth)) ?>,
    datasets: [{ 
      label: 'عدد الأوامر', 
      data: <?= json_encode(array_values($ordersByMonth)) ?>,
      borderColor: '#007bff',
      backgroundColor: 'rgba(0,123,255,0.1)',
      tension: 0.4,
      fill: true
    }]
  },
  options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('custodiesChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($custodiesByMonth)) ?>,
    datasets: [{ 
      label: 'عدد العهد', 
      data: <?= json_encode(array_values($custodiesByMonth)) ?>,
      backgroundColor: 'rgba(40, 167, 69, 0.7)',
      borderRadius: 8
    }]
  },
  options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('expensesChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($expensesByMonth)) ?>,
    datasets: [{ 
      label: 'المصروفات', 
      data: <?= json_encode(array_values($expensesByMonth)) ?>,
      backgroundColor: 'rgba(108,117,125,0.7)',
      borderRadius: 8
    }]
  },
  options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('assetsChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($assetsByPayer)) ?>,
    datasets: [{ 
      data: <?= json_encode(array_values($assetsByPayer)) ?>,
      backgroundColor: ['#ff6a00','#007bff','#28a745','#ffc107','#dc3545']
    }]
  },
  options: { plugins: { legend: { position: 'bottom' } } }
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
