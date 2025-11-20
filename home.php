<?php require __DIR__.'/partials/header.php'; ?>

<style>
/* ========================= */
/* 🔥 تصميم Ultra Premium UI - نسخة كاملة */
/* ========================= */

body {
  /*background: #f3f4f7 !important;*/
  font-family: 'Cairo', sans-serif;
}

/* ====== الهيدر الرئيسي ====== */
.hero-card {
  position: relative;
  background: linear-gradient(135deg, #ff6a00, #ff9a45, #ffd4b0);
  padding: 45px;
  border-radius: 30px;
  overflow: hidden;
  box-shadow: 0 10px 35px rgba(255, 106, 0, 0.35);
}


.hero-card::before {
  content: '';
  position: absolute;
  width: 220px;
  height: 220px;
  background: rgba(255,255,255,0.35);
  border-radius: 50%;
  top: -60px;
  right: -60px;
  filter: blur(25px);
}

.hero-title {
  font-size: 2.2rem;
  font-weight: 800;
  color: #fff;
  text-align: right;
}

.hero-sub {
  color: #fff;
  opacity: .95;
  text-align: right;
  font-size: 1.1rem;
}

/* ====== اللوجو الطائر (Animated Floating Logo) ====== */
.floating-logo {
  position: absolute;
  top: 4px;   /* 👈 بدّلها كده */
  left: 20px;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  background: #fff;
  border: 6px solid #ffb478;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: floatY 3s ease-in-out infinite;
}

.floating-logo img {
  width: 70%;
  height: 70%;
  object-fit: contain;
}

@keyframes floatY {
  0% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
  100% { transform: translateY(0); }
}

/* ====== كروت الإحصائيات Premium ====== */
.stat-card {
  background: #fff;
  padding: 25px;
  text-align: center;
  border-radius: 22px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.07);
  transition: .35s;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(255, 106, 0, 0.35);
}

.stat-icon {
  width: 60px;
  height: 60px;
  background: rgba(255,106,0,0.18);
  color: #ff6a00;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  margin: 0 auto 12px;
  position: relative;
  animation: pulseIcon 1.6s infinite;
}

@keyframes pulseIcon {
  0% { box-shadow: 0 0 0 0 rgba(255,106,0,0.4); }
  70% { box-shadow: 0 0 0 15px rgba(255,106,0,0); }
  100% { box-shadow: 0 0 0 0 rgba(255,106,0,0); }
}

.stat-title {
  font-size: .95rem;
  color: #777;
}

.stat-value {
  font-size: 1.9rem;
  font-weight: 800;
  color: #333;
}

/* ====== كروت الشارتات ====== */
.chart-card {
  padding: 22px;
  background: #fff;
  border-radius: 25px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.06);
  transition: .3s;
  height: 350px;
}

.chart-card:hover {
  box-shadow: 0 14px 35px rgba(255,106,0,0.18);
}

.chart-card h5 {
  font-weight: 700;
  color: #333;
}

.text-purple { color: #6f42c1 !important; }

/* ====== تحسينات Responsive بسيطة ====== */
@media (max-width: 768px) {
  .hero-card { padding: 25px; }
  .hero-title { font-size: 1.6rem; }
  .stat-value { font-size: 1.5rem; }
}
</style>

<?php
// نفس الاستعلامات بدون أي تغيير في المضمون
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pc = (int)$pdo->query("SELECT COUNT(*) c FROM purchases")->fetch()['c'];
$oc = (int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$ac = (int)$pdo->query("SELECT COUNT(*) c FROM assets")->fetch()['c'];
$cc = (int)$pdo->query("SELECT COUNT(*) c FROM custodies")->fetch()['c'];
$expenses_count = (int)$pdo->query("SELECT COUNT(*) c FROM expenses")->fetch()['c'];

$purchasesByMonth = $pdo->query("SELECT DATE_FORMAT(op.created_at, '%Y-%m') AS m, COUNT(DISTINCT op.id) AS c
  FROM orders_purchases op
  INNER JOIN purchases p ON op.id = p.order_id
  GROUP BY m ORDER BY m DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

$ordersByMonth = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c FROM orders GROUP BY m ORDER BY m DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

$expensesByMonth = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') m, SUM(total_amount) total
  FROM expenses GROUP BY m ORDER BY m DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesByMonth = $pdo->query("SELECT DATE_FORMAT(taken_at,'%Y-%m') m, COUNT(*) c
  FROM custodies GROUP BY m ORDER BY m DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

$assetsByPayer = $pdo->query("SELECT payer_name, COUNT(*) c FROM assets GROUP BY payer_name")->fetchAll(PDO::FETCH_KEY_PAIR);

$assetsByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS m,
           COUNT(*) AS c
    FROM assets
    GROUP BY m
    ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$assetsValueByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS m,
           SUM(total_amount) AS total
    FROM assets
    GROUP BY m
    ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$purchasesAmountByMonth = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%Y-%m') AS m,
           SUM(p.unit_all_total) AS total
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY m
    ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesValueByMonth = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%Y-%m') AS m, SUM(main_amount) AS total
    FROM custodies
    GROUP BY m
    ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="container">
  <div class="hero-card mb-5 text-end">
    <div class="floating-logo">
      <img src="<?= BASE_URL ?>/assets/logo.png" alt="Logo" />
    </div>

    <h2 class="hero-title">مرحبًا <?= esc(current_user()) ?> 👋</h2>
    <p class="hero-sub">تحكم كامل بالمشتريات، الأوامر، العُهد، المصروفات، والأصول.</p>
  </div>

  <!-- ===== كروت الإحصائيات ===== -->
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

  <!-- ===== الشارتات ===== -->
  <div class="row g-4">

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-bag text-warning me-1"></i> عدد المشتريات حسب الشهر</h5>
        <canvas id="purchasesChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-bag text-warning me-1"></i> قيمة المشتريات حسب الشهر</h5>
        <canvas id="purchasesAmountChart" height="200"></canvas>
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
        <h5 class="mb-3"><i class="bi bi-wallet2 text-success me-1"></i>عدد العهد حسب الشهر</h5>
        <canvas id="custodiesChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-wallet2 text-success me-1"></i> قيمة العهد حسب الشهر</h5>
        <canvas id="custodiesValueChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-cash-stack text-secondary me-1"></i> قيمة المصروفات حسب الشهر</h5>
        <canvas id="expensesChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-building text-success me-1"></i> عدد الأصول حسب الدافع</h5>
        <canvas id="assetsChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-building text-info me-1"></i> عدد الأصول حسب الشهر</h5>
        <canvas id="assetsMonthChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-building text-warning me-1"></i> عدد الأصول حسب الدافع</h5>
        <canvas id="assetsBarChart" height="200"></canvas>
      </div>
    </div>

    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3"><i class="bi bi-building text-warning me-1"></i> قيمة الأصول حسب الشهر</h5>
        <canvas id="assetsValueChart" height="200"></canvas>
      </div>
    </div>

  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ================================
// 🟢 إعداد المتغيرات الديناميكية للألوان
// ================================
function getChartColors() {
    const isDark = document.body.classList.contains("dark-mode");
    return {
        chartTextColor: isDark ? "#ccc" : "#111",
        chartGridColor: isDark ? "rgba(255,255,255,0.07)" : "rgba(0,0,0,0.08)",
        chartTooltipBg: isDark ? "#000" : "#fff",
        chartTooltipText: isDark ? "#fff" : "#000"
    };
}

// ================================
// 🟢 تحويل بيانات PHP إلى JS
// ================================
const purchasesLabels      = <?= json_encode(array_keys($purchasesByMonth)) ?>;
const purchasesData        = <?= json_encode(array_values($purchasesByMonth)) ?>;
const ordersLabels         = <?= json_encode(array_keys($ordersByMonth)) ?>;
const ordersData           = <?= json_encode(array_values($ordersByMonth)) ?>;
const custodiesLabels      = <?= json_encode(array_keys($custodiesByMonth)) ?>;
const custodiesData        = <?= json_encode(array_values($custodiesByMonth)) ?>;
const expensesLabels       = <?= json_encode(array_keys($expensesByMonth)) ?>;
const expensesData         = <?= json_encode(array_values($expensesByMonth)) ?>;
const assetsLabels         = <?= json_encode(array_keys($assetsByPayer)) ?>;
const assetsData           = <?= json_encode(array_values($assetsByPayer)) ?>;
const assetsMonthLabels    = <?= json_encode(array_keys($assetsByMonth)) ?>;
const assetsMonthData      = <?= json_encode(array_values($assetsByMonth)) ?>;
const assetsValueLabels    = <?= json_encode(array_keys($assetsValueByMonth)) ?>;
const assetsValueData      = <?= json_encode(array_values($assetsValueByMonth)) ?>;
const purchasesAmountLabels= <?= json_encode(array_keys($purchasesAmountByMonth)) ?>;
const purchasesAmountData  = <?= json_encode(array_values($purchasesAmountByMonth)) ?>;
const custodiesValueLabels = <?= json_encode(array_keys($custodiesValueByMonth)) ?>;
const custodiesValueData   = <?= json_encode(array_values($custodiesValueByMonth)) ?>;

// ================================
// 🟢 مصفوفة لتخزين كل الشارتات
// ================================
let charts = [];

// ================================
// 🟢 دالة إنشاء Base Options
// ================================
function getBaseOptions() {
    const { chartTextColor, chartGridColor, chartTooltipBg, chartTooltipText } = getChartColors();
    return {
        plugins: { 
            legend: { 
                display: false,
                labels: { color: chartTextColor }
            },
            tooltip: {
                backgroundColor: chartTooltipBg,
                titleColor: chartTooltipText,
                bodyColor: chartTooltipText,
                borderColor: chartGridColor,
                borderWidth: 1,
                padding: 10
            }
        },
        scales: {
            x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } },
            y: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } }
        },
        maintainAspectRatio: false
    };
}

// ================================
// 🟧 إنشاء كل الشارتات وتخزينها
// ================================
function createCharts() {
    charts = []; // إعادة تعيين المصفوفة

    // Purchases
    charts.push(new Chart(document.getElementById('purchasesChart'), {
        type: 'bar',
        data: { labels: purchasesLabels, datasets: [{ label: 'عدد المشتريات', data: purchasesData, backgroundColor: 'rgba(255,110,20,0.85)', hoverBackgroundColor:'rgba(255,130,40,1)', borderRadius: 10 }] },
        options: getBaseOptions()
    }));

    // Orders
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    const lineGradient = ordersCtx.createLinearGradient(0,0,0,350);
    lineGradient.addColorStop(0, "rgba(0,123,255,0.45)");
    lineGradient.addColorStop(1, "rgba(0,123,255,0)");
    charts.push(new Chart(ordersCtx, {
        type: 'line',
        data: { labels: ordersLabels, datasets: [{ label: 'عدد الأوامر', data: ordersData, borderColor:'#0d6efd', backgroundColor:lineGradient, tension:0.35, borderWidth:3, pointRadius:4, pointHoverRadius:6, fill:true }] },
        options: getBaseOptions()
    }));

    // Custodies
    charts.push(new Chart(document.getElementById('custodiesChart'), {
        type: 'bar',
        data: { labels: custodiesLabels, datasets:[{label:'عدد العهد', data:custodiesData, backgroundColor:'rgba(40,167,69,0.85)', hoverBackgroundColor:'rgba(60,190,90,1)', borderRadius:10}] },
        options: getBaseOptions()
    }));

    // Expenses
    charts.push(new Chart(document.getElementById('expensesChart'), {
        type: 'bar',
        data: { labels: expensesLabels, datasets:[{label:'المصروفات', data:expensesData, backgroundColor:'rgba(160,160,170,0.85)', hoverBackgroundColor:'rgba(180,180,190,1)', borderRadius:10}] },
        options: getBaseOptions()
    }));

    // Assets Doughnut
    charts.push(new Chart(document.getElementById('assetsChart'), {
        type: 'doughnut',
        data: { labels: assetsLabels, datasets:[{ data: assetsData, backgroundColor:['rgba(255,110,20,0.85)','rgba(0,123,255,0.85)','rgba(40,167,69,0.85)','rgba(255,180,20,0.85)','rgba(220,53,69,0.85)'] }] },
        options: { plugins:{ legend:{ position:'bottom', labels:{ color:getChartColors().chartTextColor } } }, maintainAspectRatio:false }
    }));

    // Assets Bar
    charts.push(new Chart(document.getElementById('assetsBarChart'), {
        type: 'bar',
        data: { labels: assetsLabels, datasets:[{label:'عدد الأصول', data:assetsData, backgroundColor:['rgba(255,110,20,0.85)','rgba(0,123,255,0.85)','rgba(40,167,69,0.85)','rgba(255,180,20,0.85)','rgba(220,53,69,0.85)'], hoverBackgroundColor:['rgba(255,130,40,1)','rgba(20,140,255,1)','rgba(60,190,90,1)','rgba(255,200,40,1)','rgba(240,70,90,1)'], borderRadius:10}] },
        options: getBaseOptions()
    }));

    // Assets By Month
    charts.push(new Chart(document.getElementById('assetsMonthChart'), {
        type: 'bar',
        data: { labels: assetsMonthLabels, datasets:[{label:'عدد الأصول', data:assetsMonthData, backgroundColor:'rgba(0,180,255,0.85)', hoverBackgroundColor:'rgba(30,200,255,1)', borderRadius:10}] },
        options: getBaseOptions()
    }));

    // Assets Value By Month
    charts.push(new Chart(document.getElementById('assetsValueChart'), {
        type:'bar',
        data: { labels: assetsValueLabels, datasets:[{label:'قيمة الأصول', data:assetsValueData, backgroundColor:'rgba(255,193,7,0.85)', hoverBackgroundColor:'rgba(255,210,40,1)', borderRadius:10}] },
        options: getBaseOptions()
    }));

    // Purchases Amount By Month
    charts.push(new Chart(document.getElementById('purchasesAmountChart'), {
        type:'bar',
        data: { labels: purchasesAmountLabels, datasets:[{label:'قيمة المشتريات', data:purchasesAmountData, backgroundColor:'rgba(255,140,30,0.85)', hoverBackgroundColor:'rgba(255,160,50,1)', borderRadius:10}] },
        options: getBaseOptions()
    }));

     charts.push(new Chart(document.getElementById('custodiesValueChart'), {
      type: 'bar',
      data: {
        labels: custodiesValueLabels,
        datasets: [{
          label: 'قيمة العهد',
          data: custodiesValueData,
          backgroundColor: 'rgba(40, 167, 69, 0.85)',      // نفس لون العهد الأساسي
          hoverBackgroundColor: 'rgba(60, 190, 90, 1)',
          borderRadius: 10
        }]
      },
      options: baseOptions
    }));
}

// إنشاء الشارتات أول مرة
createCharts();

// ================================
// 🟢 دالة تحديث الألوان مباشرة عند تبديل Dark/Light
// ================================
function updateChartsColors() {
    const { chartTextColor, chartGridColor, chartTooltipBg, chartTooltipText } = getChartColors();

    charts.forEach(chart => {
        // تحديث المحاور
        if(chart.options.scales) {
            if(chart.options.scales.x) { chart.options.scales.x.ticks.color = chartTextColor; chart.options.scales.x.grid.color = chartGridColor; }
            if(chart.options.scales.y) { chart.options.scales.y.ticks.color = chartTextColor; chart.options.scales.y.grid.color = chartGridColor; }
        }

        // تحديث الليجيند
        if(chart.options.plugins && chart.options.plugins.legend) {
            chart.options.plugins.legend.labels.color = chartTextColor;
        }

        // تحديث التولتيب
        if(chart.options.plugins && chart.options.plugins.tooltip) {
            chart.options.plugins.tooltip.backgroundColor = chartTooltipBg;
            chart.options.plugins.tooltip.titleColor = chartTooltipText;
            chart.options.plugins.tooltip.bodyColor  = chartTooltipText;
            chart.options.plugins.tooltip.borderColor= chartGridColor;
        }

        chart.update();
    });
}
</script>


<?php require __DIR__.'/partials/footer.php'; ?>