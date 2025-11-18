


<?php require __DIR__.'/partials/header.php'; ?>

<style>
/* ===== لوحة تحكم احترافية مودرن بنظام Glassmorphism + Neumorphism ===== */

body {
  background: #f5f6fa !important;
  font-family: 'Cairo', sans-serif;
}

.dashboard-wrapper {
  margin-top: 20px;
}

/* ===== كارت الترحيب ===== */
.welcome-card {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(15px);
  border-radius: 25px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
  position: relative;
  overflow: hidden;
}

.welcome-card:before {
  content: "";
  position: absolute;
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg,#ff6a00,#ffb478);
  border-radius: 50%;
  top: -50px;
  right: -50px;
  filter: blur(40px);
  opacity: .5;
}

.welcome-title {
  font-size: 2rem;
  font-weight: 800;
  color: #333;
}

.welcome-sub {
  font-size: 1.1rem;
  color: #555;
}

/* ===== الكروت الإحصائية بنظام Neumorphism ===== */
.stat-card {
  padding: 25px;
  border-radius: 20px;
  background: #fff;
  box-shadow: 8px 8px 20px #cfcfcf, -8px -8px 20px #fff;
  text-align: center;
  transition: .3s;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 4px 4px 15px #d5d5d5, -4px -4px 15px #fff;
}

.icon-wrap {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 12px;
  background: linear-gradient(135deg,#ff6a00,#ffc49d);
  box-shadow: inset 4px 4px 8px rgba(0,0,0,0.1), inset -4px -4px 8px rgba(255,255,255,0.5);
  color: #fff;
  font-size: 2rem;
}

.stat-title {
  font-size: 1rem;
  color: #777;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 800;
  color: #333;
}

/* ===== كروت الشارتات ===== */
.chart-card {
  background: #fff;
  border-radius: 22px;
  padding: 25px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.05);
  transition: .3s;
}

.chart-card:hover {
  box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}

.chart-card h5 {
  font-weight: 700;
  color: #444;
}

</style>

<div class="container dashboard-wrapper">

  <div class="welcome-card mb-5 text-end">
    <h2 class="welcome-title">مرحبًا <?= esc(current_user()) ?> 👋</h2>
    <p class="welcome-sub">إليك نظرة سريعة على أداء النظام هذا الشهر</p>
  </div>

  <div class="row g-4 mb-5">
    <?php
    $cards = [
      ["الأصناف", $pc, "bi-bag", "purchases.php"],
      ["أوامر التشغيل", $oc, "bi-gear", "orders.php"],
      ["الأصول", $ac, "bi-building", "assetes.php"],
      ["العهد", $cc, "bi-wallet2", "custodies.php"],
      ["المصروفات", $expenses_count, "bi-cash-stack", "expenses.php"],
      ["التقارير", 6, "bi-graph-up-arrow", "reports.php"],
    ];
    foreach ($cards as $c): ?>

      <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $c[3] ?>" class="text-decoration-none">
          <div class="stat-card">
            <div class="icon-wrap"><i class="bi <?= $c[2] ?>"></i></div>
            <div class="stat-title"><?= $c[0] ?></div>
            <div class="stat-value"><?= $c[1] ?></div>
          </div>
        </a>
      </div>

    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-bag text-warning"></i> المشتريات آخر 6 شهور</h5>
        <canvas id="purchasesChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-gear text-primary"></i> أوامر التشغيل</h5>
        <canvas id="ordersChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-wallet2 text-success"></i> العهد آخر 6 شهور</h5>
        <canvas id="custodiesChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-cash-stack text-secondary"></i> المصروفات حسب الشهر</h5>
        <canvas id="expensesChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5><i class="bi bi-building text-success"></i> الأصول حسب الدافع</h5>
        <canvas id="assetsChart" height="200"></canvas>
      </div>
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
