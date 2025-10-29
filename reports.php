<?php require __DIR__.'/partials/header.php'; ?>

<style>
/* 🎨 تنسيق عام */
.page-title {
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1.5rem;
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
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.8rem;
  padding: 1rem 1.5rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
  margin-right: 0;
  margin-left: 0;
}

</style>

<h3 class="page-title">
  <i class="bi bi-graph-up-arrow text-primary"></i> التقارير والتصدير
</h3>

<!-- ✅ أزرار التاريخ السريعة -->
<div class="mb-4 quick-buttons">
  <a href="?date_type=today" class="btn btn-success me-2">
    <i class="bi bi-calendar-day"></i> تقرير اليوم
  </a>
  <a href="?date_type=yesterday" class="btn btn-secondary me-2">
    <i class="bi bi-calendar2-minus"></i> تقرير أمس
  </a>
  <a href="?" class="btn btn-outline-dark">
    <i class="bi bi-x-circle"></i> إلغاء الفلتر
  </a>
</div>

<!-- 🗓️ نموذج الفلترة بنفس عرض الكروت -->
<!-- 🗓️ نموذج الفلترة بنفس محاذاة الكروت -->
<!-- 🗓️ نموذج الفلترة بنفس محاذاة الكروت تمامًا -->
<div class="container-fluid px-md-4 px-2">
  <form method="GET" class="row g-3 mb-5 align-items-end filter-form mx-0">
    <div class="col-md-4">
      <label class="form-label">من تاريخ</label>
      <input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date'] ?? '' ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">إلى تاريخ</label>
      <input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date'] ?? '' ?>">
    </div>

    <div class="col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-warning w-100" style="background-color: #ff6a00; border: none;">
        <i class="bi bi-funnel"></i> تطبيق الفلتر
      </button>
    </div>
  </form>
</div>

<?php
$filterParams = '';
if (!empty($_GET['from_date'])) $filterParams .= '&from_date=' . $_GET['from_date'];
if (!empty($_GET['to_date'])) $filterParams .= '&to_date=' . $_GET['to_date'];
if (!empty($_GET['date_type'])) $filterParams .= '&date_type=' . $_GET['date_type'];
?>

<!-- 📦 بطاقات التصدير -->
<div class="row g-4">
  <?php
  $reports = [
    ['title'=>'المشتريات','desc'=>'تصدير كامل المشتريات','excel'=>'export_purchases_excel.php','pdf'=>'export_purchases_pdf.php','icon'=>'bi-cart-check'],
    ['title'=>'أوامر التشغيل','desc'=>'تصدير آخر الأوامر','excel'=>'export_orders_excel.php','pdf'=>'export_orders_pdf.php','icon'=>'bi-gear-wide-connected'],
    ['title'=>'الأصول','desc'=>'تصدير الأصول','excel'=>'export_assets_excel.php','pdf'=>'export_assets_pdf.php','icon'=>'bi-building'],
    ['title'=>'العُهد','desc'=>'تصدير جميع العُهد','excel'=>'export_custodies_excel.php','pdf'=>'export_custodies_pdf.php','icon'=>'bi-person-badge'],
    ['title'=>'المصروفات','desc'=>'تصدير جميع المصروفات','excel'=>'export_expenses_excel.php','pdf'=>'export_expenses_pdf.php','icon'=>'bi-cash-stack'],
    ['title'=>'تقرير الضريبة','desc'=>'حساب ضريبة المشتريات، المصروفات، والأصول','excel'=>'export_vat_excel.php','pdf'=>'export_vat_pdf.php','icon'=>'bi-receipt'],
  ];
  foreach($reports as $r): ?>
  <div class="col-md-4 col-sm-6">
    <div class="card report-card p-4 text-center">
      <div class="mb-3 text-primary fs-3">
        <i class="bi <?= $r['icon'] ?>"></i>
      </div>
      <h5><?= $r['title'] ?></h5>
      <p><?= $r['desc'] ?></p>
      <div class="d-flex justify-content-between mt-3">
        <a class="btn btn-outline-success" href="<?= $r['excel'] ?>?1=1<?= $filterParams ?>">
          <i class="bi bi-file-earmark-spreadsheet"></i> Excel
        </a>
        <a class="btn btn-outline-danger" href="<?= $r['pdf'] ?>?1=1<?= $filterParams ?>">
          <i class="bi bi-filetype-pdf"></i> PDF
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>
