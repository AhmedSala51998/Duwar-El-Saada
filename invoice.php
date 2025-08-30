<?php require __DIR__.'/partials/header.php'; ?>
<?php 
$id=(int)($_GET['id']??0); 
$s=$pdo->prepare("SELECT * FROM purchases WHERE id=?"); 
$s->execute([$id]); 
$r=$s->fetch();
if(!$r){ 
  echo "<div class='alert alert-warning'>الفاتورة غير موجودة</div>"; 
  require __DIR__.'/partials/footer.php'; 
  exit; 
} 
?>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .print-area, .print-area * {
    visibility: visible;
  }
  .print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange">
    <i class="bi bi-printer"></i> طباعة
  </button>
</div>

<!-- الكارد -->
<div class="card p-4 print-area">
  <div class="d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <img src="assets/logo.svg" width="56">
      <h4 class="mb-0">فاتورة مشتريات</h4>
    </div>
    <div class="text-end">
      <div class="fw-bold">دوار السعادة</div>
      <div class="text-muted"><?= date('Y-m-d H:i') ?></div>
    </div>
  </div>
  <hr>
  <div class="row g-3">
    <div class="col-md-6">
      <div><span class="text-muted">الصنف:</span> <?= esc($r['name']) ?></div>
      <div><span class="text-muted">الكمية:</span> <?= esc($r['quantity'].' '.$r['unit']) ?></div>
      <div><span class="text-muted">السعر:</span> <?= number_format((float)$r['price'],2) ?> ريال</div>
      <div><span class="text-muted">الدافع:</span> <?= esc($r['payer_name']) ?></div>
      <div><span class="text-muted">التاريخ:</span> <?= esc($r['created_at']) ?></div>
    </div>
    <div class="col-md-6 text-end">
      <?php if($r['product_image']): ?>
        <div class="mb-2">
          <img src="uploads/<?= esc($r['product_image']) ?>" width="120" class="rounded border">
        </div>
      <?php endif; ?>

      <?php if($r['invoice_image']): ?>
        <div>
          <img src="uploads/<?= esc($r['invoice_image']) ?>" width="250" class="rounded border">
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
function printCard() {
  var content = document.querySelector('.card').outerHTML;
  var printWindow = window.open('', '', 'width=800,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>طباعة الفاتورة</title>
        <link rel="stylesheet" href="assets/bootstrap.min.css">
      </head>
      <body>${content}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}
</script>
