<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

 require __DIR__.'/partials/header.php'; require_permission('reports.view'); ?>

<style>
/* 🎨 تنسيق عام */
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
  margin-right: 10px;
  position: relative;
  transition: transform 0.6s ease; /* لتدوير الأيقونة عند hover */
}

/* حركة التدوير عند hover */
.stat-icon:hover {
  transform: rotate(360deg);
}

/* النبض المستمر */
.stat-icon::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgba(255, 106, 0, 0.2);
  animation: pulse 1.5s infinite;
  top: 0;
  left: 0;
  z-index: -1;
}

.report-card .stat-icon1 {
  width: 60px;
  height: 60px;
  background: rgba(255, 106, 0, 0.1);
  color: #ff6a00;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 1.8rem;
  margin: 0 auto 10px; /* يخليها في النص */
  position: relative;
  transition: transform 0.6s ease;
}

/* حركة التدوير عند hover */
.report-card .stat-icon1:hover {
  transform: rotate(360deg);
}

/* النبض المستمر */
.report-card .stat-icon1::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgba(255, 106, 0, 0.2);
  animation: pulse_report 1.5s infinite;
  top: 0;
  left: 0;
  z-index: -1;
}

/* تعريف النبض */
@keyframes pulse_report {
  0% {
    transform: scale(1);
    opacity: 0.6;
  }
  50% {
    transform: scale(1.4);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 0.6;
  }
}

/* تعريف النبض */
@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 0.6;
  }
  50% {
    transform: scale(1.4);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 0.6;
  }
}

/* ترتيب العنوان مع الدائرة */
.page-title {
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* 🔘 أزرار التاريخ السريعة */
.quick-buttons .btn {
  border-radius: 0.6rem;
  font-weight: 500;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  transition: 0.2s ease-in-out;
}
.quick-buttons .btn:hover {
  transform: translateY(-2px);
}

/* 📅 نموذج الفلترة */
.filter-form {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.8rem;
  padding: 1rem 1.5rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.filter-form label {
  font-weight: 500;
  color: #555;
}
.filter-form button {
  font-weight: 600;
  border-radius: 0.5rem;
  transition: 0.2s;
}
.filter-form button:hover {
  opacity: 0.9;
}

/* 📊 بطاقات التصدير */
.report-card {
  border: 1px solid #e6e9ef;
  border-radius: 1rem;
  background-color: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  transition: all 0.25s ease-in-out;
  overflow: hidden;
}
.report-card:hover {
  transform: translateY(-4px);
  border-color: #d0d4da;
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.report-card h5 {
  font-weight: 700;
  color: #333;
  margin-bottom: 0.4rem;
}
.report-card p {
  font-size: 0.9rem;
  color: #6c757d;
}
.report-card .btn {
  width: 48%;
  font-weight: 500;
  border-radius: 0.5rem;
}
.report-card .btn i {
  margin-left: 0.25rem;
}

.filter-form {
  max-width: 100%;
  width: 100%; /* 👈 خليه أعرض بنسبة بسيطة */
  margin: 0 auto; /* 👈 يوسّطه */
}
</style>

<!-- 🔹 العنوان + الأزرار في صف واحد -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
  <h3 class="page-title mb-0 d-flex align-items-center">
    <span class="stat-icon me-2">
      <i class="bi bi-graph-up-arrow"></i>
    </span>
    التقارير والتصدير
  </h3>

  <!-- ✅ أزرار التاريخ السريعة -->
  <?php if(has_permission('reports.filter')): ?>
  <div class="quick-buttons mt-3 mt-md-0">
    <a href="?date_type=today" class="btn btn-success me-2">
      <i class="bi bi-calendar-day"></i> تقرير اليوم
    </a>
    <a href="?date_type=yesterday" class="btn btn-secondary me-2">
      <i class="bi bi-calendar2-minus"></i> تقرير أمس
    </a>
    <a href="<?= basename($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-dark">
      <i class="bi bi-x-circle"></i> إلغاء الفلتر
    </a>
  </div>
  <?php endif ?>
</div>

<!-- 🗓️ نموذج الفلترة بنفس عرض الكروت -->
<!-- 🗓️ نموذج الفلترة بنفس محاذاة الكروت -->
<?php if(has_permission('reports.filter')): ?> 
<?php
// جلب قائمة الفروع من قاعدة البيانات
$branches = $pdo->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$current_branch = $_GET['branch_id'] ?? '';
?>
<div class="row g-4 mb-4">
  <div class="col-md-12">
    <form method="GET" class="row g-3 align-items-end filter-form mx-md-0 px-md-3">
      <div class="col-md-3">
        <label class="form-label">من تاريخ</label>
        <input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date'] ?? '' ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">إلى تاريخ</label>
        <input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date'] ?? '' ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">الفرع</label>
        <select name="branch_id" class="form-select">
          <option value="">كل الفروع</option>
          <?php foreach($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= $current_branch == $b['id'] ? 'selected' : '' ?>>
              <?= $b['branch_name'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
          <label class="form-label">مصدر الدفع</label>
          <select name="payment_source" class="form-select">
              <option value="">كل المصادر</option>
              <option value="كاش" <?= ($_GET['payment_source'] ?? '') == 'كاش' ? 'selected' : '' ?>>
                  كاش
              </option>
              <option value="بنك" <?= ($_GET['payment_source'] ?? '') == 'بنك' ? 'selected' : '' ?>>
                  بنك
              </option>
              <option value="عهدة" <?= ($_GET['payment_source'] ?? '') == 'عهدة' ? 'selected' : '' ?>>
                  عهدة
              </option>
          </select>
      </div>

      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-warning w-100 filter_button" style="border: none; color:#FFF">
          <i class="bi bi-funnel"></i> تطبيق الفلتر
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif ?>

<?php
$filterParams = '';
if (!empty($_GET['from_date'])) $filterParams .= '&from_date=' . $_GET['from_date'];
if (!empty($_GET['to_date'])) $filterParams .= '&to_date=' . $_GET['to_date'];
if (!empty($_GET['date_type'])) $filterParams .= '&date_type=' . $_GET['date_type'];
if (!empty($_GET['branch_id'])) $filterParams .= '&branch_id=' . $_GET['branch_id']; 
if (!empty($_GET['payment_source'])) {
    $filterParams .= '&payment_source=' . urlencode($_GET['payment_source']);
}
?>

<!-- 📦 بطاقات التصدير -->
<div class="row g-4">
  <?php
  $reports = [
    [
      'title' => 'المشتريات',
      'desc'  => 'تصدير كامل المشتريات',
      'excel' => 'export_purchases_excel.php',
      'pdf'   => 'export_purchases_pdf.php',
      'icon'  => 'bi-cart-check',
      'view_perm'  => 'reports.purchases_view',
      'excel_perm' => 'reports.report_purchases_excel',
      'pdf_perm'   => 'reports.report_purchases_pdf',
    ],
    [
      'title' => 'أوامر التشغيل',
      'desc'  => 'تصدير آخر الأوامر',
      'excel' => 'export_orders_excel.php',
      'pdf'   => 'export_orders_pdf.php',
      'icon'  => 'bi-gear-wide-connected',
      'view_perm'  => 'reports.orders_view',
      'excel_perm' => 'reports.report_orders_excel',
      'pdf_perm'   => 'reports.report_orders_pdf',
    ],
    [
      'title' => 'الأصول',
      'desc'  => 'تصدير الأصول',
      'excel' => 'export_assets_excel.php',
      'pdf'   => 'export_assets_pdf.php',
      'icon'  => 'bi-building',
      'view_perm'  => 'reports.report_assets_pdf', // مافيش عرض صريح فخلينا على pdf كحد أدنى
      'excel_perm' => 'reports.report_assets_excel',
      'pdf_perm'   => 'reports.report_assets_pdf',
    ],
    [
      'title' => 'العُهد',
      'desc'  => 'تصدير جميع العُهد',
      'excel' => 'export_custodies_excel.php',
      'pdf'   => 'export_custodies_pdf.php',
      'icon'  => 'bi-wallet2',
      'view_perm'  => 'reports.report_assets_pdf', // لو مافيش صلاحية عرض خاصة بالعهد
      'excel_perm' => 'reports.report_assets_excel', // أو غيّرها لاحقاً لو فيه كود خاص
      'pdf_perm'   => 'reports.report_assets_pdf',
    ],
    [
      'title' => 'المصروفات',
      'desc'  => 'تصدير جميع المصروفات',
      'excel' => 'export_expenses_excel.php',
      'pdf'   => 'export_expenses_pdf.php',
      'icon'  => 'bi-cash-stack',
      'view_perm'  => 'reports.report_expenses_pdf',
      'excel_perm' => 'reports.report_expenses_excel',
      'pdf_perm'   => 'reports.report_expenses_pdf',
    ],
    [
      'title' => 'تقرير الضريبة',
      'desc'  => 'حساب ضريبة المشتريات، المصروفات، والأصول',
      'excel' => 'export_vat_excel.php',
      'pdf'   => 'export_vat_pdf.php',
      'icon'  => 'bi-receipt',
      'view_perm'  => 'reports.vat_view',
      'excel_perm' => 'reports.report_vat_excel',
      'pdf_perm'   => 'reports.report_vat_pdf',
    ],
  ];

  foreach($reports as $r):
    if (has_permission($r['view_perm'])):
  ?>
  <div class="col-md-4 col-sm-6">
    <div class="card report-card p-4 text-center">
      <div class="mb-3 text-primary fs-3">
        <i class="bi <?= $r['icon'] ?> stat-icon1"></i>
      </div>
      <h5><?= $r['title'] ?></h5>
      <p><?= $r['desc'] ?></p>
      <div class="d-flex justify-content-between mt-3">
        <?php if(has_permission($r['excel_perm'])): ?>
        <a class="btn btn-outline-success" href="<?= $r['excel'] ?>?1=1<?= $filterParams ?>">
          <i class="bi bi-file-earmark-spreadsheet"></i> Excel
        </a>
        <?php endif; ?>

        <?php if(has_permission($r['pdf_perm'])): ?>
        <a class="btn btn-outline-danger" href="<?= $r['pdf'] ?>?1=1<?= $filterParams ?>">
          <i class="bi bi-filetype-pdf"></i> PDF
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php 
    endif;
  endforeach; 
  ?>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>
