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

/*$purchasesByMonth = $pdo->query("SELECT DATE_FORMAT(op.created_at, '%Y-%m') AS m, COUNT(DISTINCT op.id) AS c
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

$expensesCountByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS m, COUNT(*) AS c
    FROM expenses
    GROUP BY m
    ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);*/


// ===================================
// 🟢 PHP: تجهيز البيانات لكل فلتر
// ===================================

// Purchases
$purchasesByWeek = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%x-%v') AS w, COUNT(DISTINCT op.id) AS c
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$purchasesByMonth = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%Y-%m') AS m, COUNT(DISTINCT op.id) AS c
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$purchasesByYear = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%Y') AS y, COUNT(DISTINCT op.id) AS c
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Orders
$ordersByWeek = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%x-%v') AS w, COUNT(*) AS c
    FROM orders
    GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$ordersByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS c
    FROM orders
    GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$ordersByYear = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y') AS y, COUNT(*) AS c
    FROM orders
    GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Expenses (عدد)
$expensesCountByWeek = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%x-%v') AS w, COUNT(*) AS c
    FROM expenses
    GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$expensesCountByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS c
    FROM expenses
    GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$expensesCountByYear = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y') AS y, COUNT(*) AS c
    FROM expenses
    GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Expenses (قيمة)
$expensesValueByWeek = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%x-%v') AS w, SUM(total_amount) AS total
    FROM expenses
    GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$expensesValueByMonth = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, SUM(total_amount) AS total
    FROM expenses
    GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$expensesValueByYear = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y') AS y, SUM(total_amount) AS total
    FROM expenses
    GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Custodies (عدد)
$custodiesByWeek = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%x-%v') AS w, COUNT(*) AS c
    FROM custodies GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesByMonth = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%Y-%m') AS m, COUNT(*) AS c
    FROM custodies GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesByYear = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%Y') AS y, COUNT(*) AS c
    FROM custodies GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Custodies (قيمة)
$custodiesValueByWeek = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%x-%v') AS w, SUM(main_amount) AS total
    FROM custodies GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesValueByMonth = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%Y-%m') AS m, SUM(main_amount) AS total
    FROM custodies GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$custodiesValueByYear = $pdo->query("
    SELECT DATE_FORMAT(taken_at,'%Y') AS y, SUM(main_amount) AS total
    FROM custodies GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);


// المشتريات (قيمة)
$purchasesAmountByWeek = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%x-%v') AS w, SUM(p.unit_all_total) AS total
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY w ORDER BY w DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$purchasesAmountByMonth = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%Y-%m') AS m, SUM(p.unit_all_total) AS total
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY m ORDER BY m DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$purchasesAmountByYear = $pdo->query("
    SELECT DATE_FORMAT(op.created_at, '%Y') AS y, SUM(p.unit_all_total) AS total
    FROM orders_purchases op
    INNER JOIN purchases p ON op.id = p.order_id
    GROUP BY y ORDER BY y DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Assets عدد حسب الدافع
$assetsByWeek = $pdo->query("SELECT DATE_FORMAT(created_at,'%x-%v') AS w, COUNT(*) AS c FROM assets GROUP BY w ORDER BY w DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$assetsByMonth = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') AS m, COUNT(*) AS c FROM assets GROUP BY m ORDER BY m DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$assetsByYear = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y') AS y, COUNT(*) AS c FROM assets GROUP BY y ORDER BY y DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

// Assets عدد حسب الشهر
$assetsMonthByWeek = $assetsByWeek;
$assetsMonthByMonth = $assetsByMonth;
$assetsMonthByYear = $assetsByYear;

// Assets عدد حسب الدافع (Bar)
$assetsBarByWeek = $assetsByWeek;
$assetsBarByMonth = $assetsByMonth;
$assetsBarByYear = $assetsByYear;

// Assets قيمة حسب الشهر
$assetsValueByWeek = $pdo->query("SELECT DATE_FORMAT(created_at,'%x-%v') AS w, SUM(total_amount) AS total FROM assets GROUP BY w ORDER BY w DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$assetsValueByMonth = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') AS m, SUM(total_amount) AS total FROM assets GROUP BY m ORDER BY m DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$assetsValueByYear = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y') AS y, SUM(total_amount) AS total FROM assets GROUP BY y ORDER BY y DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

// ===========================
// عدد الأصول حسب الدافع (Payer)
// ===========================
// ====================== PHP ======================

// جلب البيانات من قاعدة البيانات باستخدام نفس طريقة التاريخ للفترات
$assetsByWeek_payer_raw = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%x-%v') AS period, payer_name AS label, COUNT(*) AS c
    FROM assets
    GROUP BY period, payer_name
    ORDER BY period DESC
")->fetchAll(PDO::FETCH_ASSOC);

$assetsByMonth_payer_raw = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS period, payer_name AS label, COUNT(*) AS c
    FROM assets
    GROUP BY period, payer_name
    ORDER BY period DESC
")->fetchAll(PDO::FETCH_ASSOC);

$assetsByYear_payer_raw = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%Y') AS period, payer_name AS label, COUNT(*) AS c
    FROM assets
    GROUP BY period, payer_name
    ORDER BY period DESC
")->fetchAll(PDO::FETCH_ASSOC);

// دالة لتحويل البيانات للشكل المطلوب
function groupByPeriod($raw) {
    $result = [];
    foreach ($raw as $row) {
        $period = $row['period'];
        $label = $row['label'];
        $count = (int)$row['c'];
        if (!isset($result[$period])) $result[$period] = [];
        $result[$period][$label] = $count;
    }
    return $result;
}

$assetsByWeek_payer  = groupByPeriod($assetsByWeek_payer_raw);
$assetsByMonth_payer = groupByPeriod($assetsByMonth_payer_raw);
$assetsByYear_payer  = groupByPeriod($assetsByYear_payer_raw);

// مصفوفة JS جاهزة
$assetsDataBy_payer = [
    'week'  => $assetsByWeek_payer,
    'month' => $assetsByMonth_payer,
    'year'  => $assetsByYear_payer
];
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

    <!-- عدد المشتريات -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-bag text-warning me-1"></i> عدد المشتريات</h5>
          <select class="form-select form-select-sm" id="purchasesFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="purchasesChart" height="200"></canvas>
      </div>
    </div>

    <!-- قيمة المشتريات -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-bag text-warning me-1"></i> قيمة المشتريات</h5>
          <select class="form-select form-select-sm" id="purchasesAmountFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="purchasesAmountChart" height="200"></canvas>
      </div>
    </div>

    <!-- أوامر التشغيل -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-gear text-primary me-1"></i> أوامر التشغيل</h5>
          <select class="form-select form-select-sm" id="ordersFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="ordersChart" height="200"></canvas>
      </div>
    </div>

    <!-- عدد العهد -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-wallet2 text-success me-1"></i> عدد العهد</h5>
          <select class="form-select form-select-sm" id="custodiesFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="custodiesChart" height="200"></canvas>
      </div>
    </div>

    <!-- قيمة العهد -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-wallet2 text-success me-1"></i> قيمة العهد</h5>
          <select class="form-select form-select-sm" id="custodiesValueFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="custodiesValueChart" height="200"></canvas>
      </div>
    </div>

    <!-- عدد المصروفات -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-cash-stack text-secondary me-1"></i> عدد المصروفات</h5>
          <select class="form-select form-select-sm" id="expensesCountFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="expensesCountChart" height="200"></canvas>
      </div>
    </div>

    <!-- قيمة المصروفات -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-cash-stack text-secondary me-1"></i> قيمة المصروفات</h5>
          <select class="form-select form-select-sm" id="expensesValueFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="expensesChart" height="200"></canvas>
      </div>
    </div>

    <!-- عدد الأصول حسب الدافع -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-building text-success me-1"></i> عدد الأصول حسب الدافع</h5>
          <select class="form-select form-select-sm" id="assetsFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="assetsChart" height="200"></canvas>
      </div>
    </div>

    <!-- عدد الأصول حسب الشهر -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-building text-info me-1"></i> عدد الأصول حسب الشهر</h5>
          <select class="form-select form-select-sm" id="assetsMonthFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="assetsMonthChart" height="200"></canvas>
      </div>
    </div>

    <!-- عدد الأصول حسب الدافع (Bar) -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-building text-warning me-1"></i> عدد الأصول حسب الدافع</h5>
          <select class="form-select form-select-sm" id="assetsBarFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="assetsBarChart" height="200"></canvas>
      </div>
    </div>

    <!-- قيمة الأصول حسب الشهر -->
    <div class="col-md-6">
      <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-building text-warning me-1"></i> قيمة الأصول حسب الشهر</h5>
          <select class="form-select form-select-sm" id="assetsValueFilter" style="width:auto">
            <option value="week">أسبوع</option>
            <option value="month" selected>شهر</option>
            <option value="year">سنة</option>
          </select>
        </div>
        <canvas id="assetsValueChart" height="200"></canvas>
      </div>
    </div>

  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ================================
// 🟢 Get colors based on Dark/Light
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
// 🟢 Base Options
// ================================
function getBaseOptions() {
    const { chartTextColor, chartGridColor, chartTooltipBg, chartTooltipText } = getChartColors();
    return {
        plugins: { 
            legend: { labels: { color: chartTextColor } },
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
// 🟢 Convert PHP arrays to JS
// ================================
const purchasesDataBy = {
  week: <?= json_encode($purchasesByWeek) ?>,
  month: <?= json_encode($purchasesByMonth) ?>,
  year: <?= json_encode($purchasesByYear) ?>
};

const ordersDataBy = {
  week: <?= json_encode($ordersByWeek) ?>,
  month: <?= json_encode($ordersByMonth) ?>,
  year: <?= json_encode($ordersByYear) ?>
};

const expensesCountDataBy = {
  week: <?= json_encode($expensesCountByWeek) ?>,
  month: <?= json_encode($expensesCountByMonth) ?>,
  year: <?= json_encode($expensesCountByYear) ?>
};

const expensesValueDataBy = {
  week: <?= json_encode($expensesValueByWeek) ?>,
  month: <?= json_encode($expensesValueByMonth) ?>,
  year: <?= json_encode($expensesValueByYear) ?> 
};

const custodiesDataBy = {
  week: <?= json_encode($custodiesByWeek) ?>,
  month: <?= json_encode($custodiesByMonth) ?>,
  year: <?= json_encode($custodiesByYear) ?>
};

const custodiesValueDataBy = {
  week: <?= json_encode($custodiesValueByWeek) ?>,
  month: <?= json_encode($custodiesValueByMonth) ?>,
  year: <?= json_encode($custodiesValueByYear) ?>
};

const purchasesAmountDataBy = {
  week: <?= json_encode($purchasesAmountByWeek) ?>,
  month: <?= json_encode($purchasesAmountByMonth) ?>,
  year: <?= json_encode($purchasesAmountByYear) ?>
};

// ================================
// 🟢 Charts array
// ================================
let charts = {};

// ================================
// 🟢 Create Charts
// ================================
function createChart(canvasId, dataBy, label, color) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = Object.keys(dataBy.month); // default month
    const data = Object.values(dataBy.month);

    const chart = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label, data, backgroundColor: color, borderRadius: 10 }] },
        options: getBaseOptions()
    });

    // store chart
    charts[canvasId] = chart;

    return chart;
}

// إنشاء كل الشارتات
createChart('purchasesChart', purchasesDataBy, 'عدد المشتريات', 'rgba(255,110,20,0.85)');
createChart('ordersChart', ordersDataBy, 'عدد الأوامر', 'rgba(0,123,255,0.85)');
createChart('expensesCountChart', expensesCountDataBy, 'عدد المصروفات', 'rgba(108,117,125,0.85)');
createChart('expensesChart', expensesValueDataBy, 'قيمة المصروفات', 'rgba(160,160,170,0.85)');
createChart('custodiesChart', custodiesDataBy, 'عدد العهد', 'rgba(40,167,69,0.85)');
createChart('custodiesValueChart', custodiesValueDataBy, 'قيمة العهد', 'rgba(40,167,69,0.85)');
// المشتريات بالمبالغ
createChart('purchasesAmountChart', purchasesAmountDataBy, 'قيمة المشتريات', 'rgba(255,140,30,0.85)');
setupFilter('purchasesAmountFilter', 'purchasesAmountChart', purchasesAmountDataBy);


// ================================
// 🟢 Filter Event Listeners
// ================================
function setupFilter(filterId, canvasId, dataBy) {
    document.getElementById(filterId).addEventListener('change', function(){
        const value = this.value; // week/month/year
        const chart = charts[canvasId];
        chart.data.labels = Object.keys(dataBy[value]);
        chart.data.datasets[0].data = Object.values(dataBy[value]);
        chart.update();
    });
}

// إعداد كل الفلاتر
setupFilter('purchasesFilter', 'purchasesChart', purchasesDataBy);
setupFilter('ordersFilter', 'ordersChart', ordersDataBy);
setupFilter('expensesCountFilter', 'expensesCountChart', expensesCountDataBy);
setupFilter('expensesValueFilter', 'expensesChart', expensesValueDataBy);
setupFilter('custodiesFilter', 'custodiesChart', custodiesDataBy);
setupFilter('custodiesValueFilter', 'custodiesValueChart', custodiesValueDataBy);

// ================================
// 🟢 Dark/Light Mode update
// ================================
function updateChartsColors() {
    Object.values(charts).forEach(chart => {
        const { chartTextColor, chartGridColor, chartTooltipBg, chartTooltipText } = getChartColors();
        chart.options.scales.x.ticks.color = chartTextColor;
        chart.options.scales.x.grid.color = chartGridColor;
        chart.options.scales.y.ticks.color = chartTextColor;
        chart.options.scales.y.grid.color = chartGridColor;

        chart.options.plugins.legend.labels.color = chartTextColor;
        chart.options.plugins.tooltip.backgroundColor = chartTooltipBg;
        chart.options.plugins.tooltip.titleColor = chartTooltipText;
        chart.options.plugins.tooltip.bodyColor = chartTooltipText;
        chart.options.plugins.tooltip.borderColor = chartGridColor;
        chart.update();
    });
}

// Example: toggle dark mode button
document.querySelectorAll('.toggle-dark-mode').forEach(btn => {
    btn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        updateChartsColors();
    });
});

const assetsDataBy = { week: <?= json_encode($assetsByWeek) ?>, month: <?= json_encode($assetsByMonth) ?>, year: <?= json_encode($assetsByYear) ?> };
const assetsMonthDataBy = { week: <?= json_encode($assetsMonthByWeek) ?>, month: <?= json_encode($assetsMonthByMonth) ?>, year: <?= json_encode($assetsMonthByYear) ?> };
const assetsBarDataBy = { week: <?= json_encode($assetsBarByWeek) ?>, month: <?= json_encode($assetsBarByMonth) ?>, year: <?= json_encode($assetsBarByYear) ?> };
const assetsValueDataBy = { week: <?= json_encode($assetsValueByWeek) ?>, month: <?= json_encode($assetsValueByMonth) ?>, year: <?= json_encode($assetsValueByYear) ?> };

const assetsDataBy_payer = { week: <?= json_encode($assetsByWeek_payer) ?>, month: <?= json_encode($assetsByMonth_payer) ?>, year: <?= json_encode($assetsByYear_payer) ?> };

function createChartWithFilter(canvasId, dataBy, label, color, filterId) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const chart = new Chart(ctx, {
        type:'bar',
        data: { labels:Object.keys(dataBy.month), datasets:[{label,label,data:Object.values(dataBy.month),backgroundColor:color,borderRadius:10}]},
        options:getBaseOptions()
    });
    charts[canvasId] = chart;

    // Filter
    document.getElementById(filterId).addEventListener('change', function(){
        const period = this.value;
        chart.data.labels = Object.keys(dataBy[period]);
        chart.data.datasets[0].data = Object.values(dataBy[period]);
        chart.update();
    });
}

// إنشاء شارتات الأصول
createChartWithFilter('assetsMonthChart', assetsMonthDataBy, 'عدد الأصول حسب الشهر', 'rgba(0,123,255,0.85)', 'assetsMonthFilter');
createChartWithFilter('assetsValueChart', assetsValueDataBy, 'قيمة الأصول حسب الشهر', 'rgba(255,110,20,0.85)', 'assetsValueFilter');

const assetsDataBy_payer = <?= json_encode($assetsDataBy_payer) ?>;

// ألوان Pie
const pieColors = [
    'rgba(40,167,69,0.85)',
    'rgba(0,123,255,0.85)',
    'rgba(255,193,7,0.85)',
    'rgba(255,110,20,0.85)',
    'rgba(108,117,125,0.85)',
    'rgba(160,160,170,0.85)'
];

// قاعدة الخيارات
function getBaseOptionss() {
    return {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                mode: 'index'
            }
        }
    };
}

// ========== Pie Chart ==========
function createChartWithFilterPie(canvasId, dataBy, label, colors, filterId) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    // اختر أول period موجود فيه بيانات
    let defaultPeriod, defaultSubPeriod;
    outer: for (let pType in dataBy) {
        for (let p in dataBy[pType]) {
            defaultPeriod = pType;
            defaultSubPeriod = p;
            break outer;
        }
    }

    if (!defaultPeriod) {
        console.warn('No data available for Pie chart');
        return;
    }

    const chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(dataBy[defaultPeriod][defaultSubPeriod]),
            datasets: [{
                label: label,
                data: Object.values(dataBy[defaultPeriod][defaultSubPeriod]),
                backgroundColor: colors
            }]
        },
        options: getBaseOptionss()
    });

    charts[canvasId] = chart;

    // Filter
    const filterEl = document.getElementById(filterId);
    if (filterEl) {
        filterEl.addEventListener('change', function() {
            const period = this.value;
            if (dataBy[defaultPeriod][period]) {
                chart.data.labels = Object.keys(dataBy[defaultPeriod][period]);
                chart.data.datasets[0].data = Object.values(dataBy[defaultPeriod][period]);
                chart.update();
            }
        });
    }
}

// ========== Bar Chart ==========
function createChartWithFilterBar(canvasId, dataBy, label, color, filterId) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    // اختر أول period موجود فيه بيانات
    let defaultPeriod, defaultSubPeriod;
    outer: for (let pType in dataBy) {
        for (let p in dataBy[pType]) {
            defaultPeriod = pType;
            defaultSubPeriod = p;
            break outer;
        }
    }

    if (!defaultPeriod) {
        console.warn('No data available for Bar chart');
        return;
    }

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(dataBy[defaultPeriod][defaultSubPeriod]),
            datasets: [{
                label: label,
                data: Object.values(dataBy[defaultPeriod][defaultSubPeriod]),
                backgroundColor: color,
                borderRadius: 10
            }]
        },
        options: getBaseOptionss()
    });

    charts[canvasId] = chart;

    const filterEl = document.getElementById(filterId);
    if (filterEl) {
        filterEl.addEventListener('change', function() {
            const period = this.value;
            if (dataBy[defaultPeriod][period]) {
                chart.data.labels = Object.keys(dataBy[defaultPeriod][period]);
                chart.data.datasets[0].data = Object.values(dataBy[defaultPeriod][period]);
                chart.update();
            }
        });
    }
}

// ====================== إنشاء الشارتات ======================

createChartWithFilterPie('assetsChart', assetsDataBy_payer, 'عدد الأصول حسب الدافع', pieColors, 'assetsFilter');
createChartWithFilterBar('assetsBarChart', assetsDataBy_payer, 'عدد الأصول حسب الدافع', 'rgba(255,193,7,0.85)', 'assetsBarFilter');
</script>


<?php require __DIR__.'/partials/footer.php'; ?>