<?php require __DIR__.'/partials/header.php'; ?>
<h3 class="mb-3">التقارير والتصدير</h3>
<div class="row g-3">
  <div class="col-md-4"><div class="card p-3 h-100">
    <h5>المشتريات</h5>
    <p class="text-muted small">تصدير كامل المشتريات.</p>
    <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_purchases_excel.php"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_purchases_pdf.php"><i class="bi bi-filetype-pdf"></i> PDF</a>
  </div></div>
  <div class="col-md-4"><div class="card p-3 h-100">
    <h5>أوامر التشغيل</h5>
    <p class="text-muted small">تصدير آخر الأوامر.</p>
    <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_orders_excel.php"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_orders_pdf.php"><i class="bi bi-filetype-pdf"></i> PDF</a>
  </div></div>
  <div class="col-md-4"><div class="card p-3 h-100">
    <h5>العُهد</h5>
    <p class="text-muted small">تصدير الأصول.</p>
    <a style="margin-bottom:5px" class="btn btn-outline-dark" href="export_assets_excel.php"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_assets_pdf.php"><i class="bi bi-filetype-pdf"></i> PDF</a>
  </div></div>
</div>
<?php require __DIR__.'/partials/footer.php'; ?>
