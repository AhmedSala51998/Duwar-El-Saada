<?php require __DIR__.'/partials/header.php'; ?>

<?php
// العدادات الحالية
$pc = (int)$pdo->query("SELECT COUNT(*) c FROM purchases")->fetch()['c'];
$oc = (int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$ac = (int)$pdo->query("SELECT COUNT(*) c FROM assets")->fetch()['c'];

// الإحصائيات الجديدة
$gf_count = (int)$pdo->query("SELECT COUNT(*) c FROM gov_fees")->fetch()['c'];
$subs_count = (int)$pdo->query("SELECT COUNT(*) c FROM subscriptions")->fetch()['c'];
$rentals_count = (int)$pdo->query("SELECT COUNT(*) c FROM rentals")->fetch()['c'];

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

// الرسوم الحكومية حسب الدافع
$govFeesByPayer = $pdo->query("
  SELECT payer, COUNT(*) c FROM gov_fees GROUP BY payer
")->fetchAll(PDO::FETCH_KEY_PAIR);

// الاشتراكات حسب الدافع
$subsByPayer = $pdo->query("
  SELECT payer, COUNT(*) c FROM subscriptions GROUP BY payer
")->fetchAll(PDO::FETCH_KEY_PAIR);

// الإيجارات حسب الدافع
$rentalsByPayer = $pdo->query("
  SELECT payer, COUNT(*) c FROM rentals GROUP BY payer
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-4 border-0 shadow-sm" style="background:linear-gradient(135deg, rgba(255,106,0,.9), rgba(255,180,120,.9));color:#fff">
      <h4>أهلًا <?= esc(current_user()) ?> 👋</h4>
      <div>أدِر المشتريات، الأوامر، العُهد، الرسوم، الاشتراكات والإيجارات بسهولة.</div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="row g-3">
      <div class="col-md-2">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">أصناف</div>
          <div class="fs-3"><?= $pc ?></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">أوامر</div>
          <div class="fs-3"><?= $oc ?></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">العُهد</div>
          <div class="fs-3"><?= $ac ?></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">الرسوم الحكومية</div>
          <div class="fs-3"><?= $gf_count ?></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">الاشتراكات</div>
          <div class="fs-3"><?= $subs_count ?></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card p-3 shadow-sm">
          <div class="text-muted">الإيجارات</div>
          <div class="fs-3"><?= $rentals_count ?></div>
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
  <div class="col-md-6">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">العُهد حسب الدافع</h5>
      <canvas id="assetsChart" height="200"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">الرسوم الحكومية حسب الدافع</h5>
      <canvas id="govFeesChart" height="200"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">الاشتراكات حسب الدافع</h5>
      <canvas id="subsChart" height="200"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">الإيجارات حسب الدافع</h5>
      <canvas id="rentalsChart" height="200"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('purchasesChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($purchasesByMonth)) ?>,
    datasets: [{ label: 'عدد المشتريات', data: <?= json_encode(array_values($purchasesByMonth)) ?>, backgroundColor: 'rgba(255, 106, 0, 0.7)' }]
  }
});

new Chart(document.getElementById('ordersChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_keys($ordersByMonth)) ?>,
    datasets: [{ label: 'عدد الأوامر', data: <?= json_encode(array_values($ordersByMonth)) ?>, borderColor: 'rgba(0,123,255,0.8)', fill:false, tension:0.3 }]
  }
});

new Chart(document.getElementById('assetsChart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($assetsByPayer)) ?>,
    datasets: [{ data: <?= json_encode(array_values($assetsByPayer)) ?>, backgroundColor: ['#ff6a00','#007bff','#28a745','#ffc107','#dc3545'] }]
  }
});

new Chart(document.getElementById('govFeesChart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($govFeesByPayer)) ?>,
    datasets: [{ data: <?= json_encode(array_values($govFeesByPayer)) ?>, backgroundColor: ['#6f42c1','#6610f2','#20c997','#fd7e14','#dc3545'] }]
  }
});

new Chart(document.getElementById('subsChart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($subsByPayer)) ?>,
    datasets: [{ data: <?= json_encode(array_values($subsByPayer)) ?>, backgroundColor: ['#0dcaf0','#198754','#ffc107','#fd7e14','#dc3545'] }]
  }
});

new Chart(document.getElementById('rentalsChart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_keys($rentalsByPayer)) ?>,
    datasets: [{ data: <?= json_encode(array_values($rentalsByPayer)) ?>, backgroundColor: ['#0d6efd','#6c757d','#198754','#fd7e14','#dc3545'] }]
  }
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
