<?php require __DIR__.'/partials/header.php'; ?>
<h3 class="mb-3">التقارير والتصدير</h3>

<!-- ✅ أزرار سريعة لاختيار التاريخ -->
<div class="mb-3">
  <a href="?date_type=today" class="btn btn-success me-2">
    <i class="bi bi-calendar-day"></i> تقرير اليوم
  </a>
  <a href="?date_type=yesterday" class="btn btn-secondary me-2">
    <i class="bi bi-calendar2-minus"></i> تقرير أمس
  </a>
  <a href="?" class="btn btn-outline-dark">
    <i class="bi bi-calendar-x"></i> إلغاء الفلتر
  </a>
</div>

<!-- فلترة بالتاريخ -->
<form method="GET" class="row g-3 mb-4 align-items-end">
  <div class="col-md-3">
    <label class="form-label">من تاريخ</label>
    <input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date'] ?? '' ?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">إلى تاريخ</label>
    <input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date'] ?? '' ?>">
  </div>

  <div class="col-md-2 d-flex">
    <button type="submit" class="btn w-100" style="background-color: #ff6a00; border: 1px solid #ff6a00; color: white;">
      تطبيق الفلتر
    </button>
  </div>
</form>

<?php
// إعداد متغير الفلترة للروابط
$filterParams = '';
if (!empty($_GET['from_date'])) $filterParams .= '&from_date=' . $_GET['from_date'];
if (!empty($_GET['to_date'])) $filterParams .= '&to_date=' . $_GET['to_date'];
if (!empty($_GET['date_type'])) $filterParams .= '&date_type=' . $_GET['date_type'];
?>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5>المشتريات</h5>
      <p class="text-muted small">تصدير كامل المشتريات.</p>
      <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_purchases_excel.php?1=1<?= $filterParams ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
      <a class="btn btn-outline-dark" href="export_purchases_pdf.php?1=1<?= $filterParams ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5>أوامر التشغيل</h5>
      <p class="text-muted small">تصدير آخر الأوامر.</p>
      <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_orders_excel.php?1=1<?= $filterParams ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
      <a class="btn btn-outline-dark" href="export_orders_pdf.php?1=1<?= $filterParams ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5>الأصول</h5>
      <p class="text-muted small">تصدير الأصول.</p>
      <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_assets_excel.php?1=1<?= $filterParams ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
      <a class="btn btn-outline-dark" href="export_assets_pdf.php?1=1<?= $filterParams ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5>العُهد</h5>
      <p class="text-muted small">تصدير جميع العُهد.</p>
      <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_custodies_excel.php?1=1<?= $filterParams ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
      <a class="btn btn-outline-dark" href="export_custodies_pdf.php?1=1<?= $filterParams ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5>المصروفات</h5>
      <p class="text-muted small">تصدير جميع المصروفات.</p>
      <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_expenses_excel.php?1=1<?= $filterParams ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
      <a class="btn btn-outline-dark" href="export_expenses_pdf.php?1=1<?= $filterParams ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5>تقريب الضريبة</h5>
      <p class="text-muted small">حساب ضريبة المشتريات، المصروفات، والأصول.</p>
      <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_vat_excel.php?1=1<?= $filterParams ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
      <a class="btn btn-outline-dark" href="export_vat_pdf.php?1=1<?= $filterParams ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
  </div>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>
