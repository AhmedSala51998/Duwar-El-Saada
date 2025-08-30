<?php require __DIR__.'/partials/header.php'; ?>
<?php
$pc = (int)$pdo->query("SELECT COUNT(*) c FROM purchases")->fetch()['c'];
$oc = (int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$ac = (int)$pdo->query("SELECT COUNT(*) c FROM assets")->fetch()['c'];

// المشتريات بالشهور (آخر 6 شهور)
$purchasesByMonth = $pdo->query("
  SELECT DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c 
  FROM purchases 
  GROUP BY m ORDER BY m DESC LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

// أوامر التشغيل حسب الشهر
$ordersByMonth = $pdo->query("
  SELECT DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c 
  FROM orders 
  GROUP BY m ORDER BY m DESC LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

// العهد حسب الدافع
$assetsByPayer = $pdo->query("
  SELECT payer_name, COUNT(*) c FROM assets GROUP BY payer_name
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-4 border-0 shadow-sm" style="background:linear-gradient(135deg, rgba(255,106,0,.9), rgba(255,180,120,.9));color:#fff">
      <h4>أهلًا <?= esc(current_user()) ?> 👋</h4>
      <div>أدِر المشتريات، الأوامر، والعهد بسهولة.</div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">أصناف</div>
          <div class="fs-3"><?= $pc ?></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">أوامر</div>
          <div class="fs-3"><?= $oc ?></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">العُهد</div>
          <div class="fs-3"><?= $ac ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<hr>

<div class="row g-4">
  <div class="col-md-6">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">المشتريات (آخر 6 شهور)</h5>
      <canvas id="purchasesChart" height="200"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">أوامر التشغيل</h5>
      <canvas id="ordersChart" height="200"></canvas>
    </div>
  </div>
  <div class="col-md-12">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">العُهد حسب الدافع</h5>
      <canvas id="assetsChart" height="200"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const purchasesCtx = document.getElementById('purchasesChart').getContext('2d');
new Chart(purchasesCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($purchasesByMonth)) ?>,
    datasets: [{
      label: 'عدد المشتريات',
      data: <?= json_encode(array_values($purchasesByMonth)) ?>,
      backgroundColor: 'rgba(255, 106, 0, 0.7)'
    }]
  }
});

const ordersCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(ordersCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_keys($ordersByMonth)) ?>,
    datasets: [{
      label: 'عدد الأوامر',
      data: <?= json_encode(array_values($ordersByMonth)) ?>,
      borderColor: 'rgba(0, 123, 255, 0.8)',
      fill: false,
      tension: 0.3
    }]
  }
});

const assetsCtx = document.getElementById('assetsChart').getContext('2d');
new Chart(assetsCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($assetsByPayer)) ?>,
    datasets: [{
      data: <?= json_encode(array_values($assetsByPayer)) ?>,
      backgroundColor: ['#ff6a00','#007bff','#28a745','#ffc107','#dc3545']
    }]
  }
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
