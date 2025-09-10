<?php require __DIR__.'/partials/header.php'; ?>
<?php 
$id = (int)($_GET['id'] ?? 0); 
$s = $pdo->prepare("SELECT * FROM purchases WHERE id=?"); 
$s->execute([$id]); 
$r = $s->fetch();

if (!$r) { 
    echo "<div class='alert alert-warning'>الفاتورة غير موجودة</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
} 
?>

<style>
/* ------------------------- الطباعة ------------------------- */
@media print {
  body {
    background: #fff;
    margin: 0;
    padding: 0;
  }
  body * {
    visibility: hidden;
  }
  .print-area, .print-area * {
    visibility: visible;
  }
  .print-area {
    position: relative;
    max-width: 1200px;    /* نفس العرض اللي ظاهر في الشاشة */
    margin: 0 auto;
    box-shadow: none;
    border: none;
    padding: 30px;
    transform: scale(1); /* منع أي تصغير تلقائي */
  }

  /* إزالة حدود الصفحة البيضاء الافتراضية */
  @page {
    size: A4 landscape;   /* لو محتاج العرض أكبر زي الصورة الثانية */
    margin: 0;
  }
}


/* ------------------------- كارد احترافي ------------------------- */
.print-area {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
  padding: 30px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

.print-area h4 {
  color: #FF8C00; /* برتقالي عصري */
  font-weight: 700;
}

.print-area .text-muted {
  color: #666 !important;
}

.print-area img {
  max-width: 100%;
  border-radius: 8px;
  border: 1px solid #eee;
}

.btn-orange {
  background: #FF8C00;
  color: #fff;
  border: none;
  transition: all 0.3s;
}

.btn-orange:hover {
  background: #e57700;
  color: #fff;
}

/* ------------------------- Responsive ------------------------- */
@media (max-width: 768px) {
  .print-area .row > [class*="col-"] {
    text-align: center !important;
  }
  .print-area .text-end {
    text-align: center !important;
  }
  .d-flex.justify-content-between {
    flex-direction: column;
    align-items: center;
    gap: 15px;
  }
}
</style>

<div class="d-print-none mb-3">
  <button onclick="printCard()" class="btn btn-orange">
    <i class="bi bi-printer"></i> طباعة
  </button>
</div>

<!-- ------------------------- الكارد ------------------------- -->
<div class="card p-4 print-area">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
      <img src="assets/logo.svg" width="60">
      <h4 class="mb-0">فاتورة مشتريات</h4>
    </div>
    <div class="text-end">
      <div class="fw-bold">دوار السعادة</div>
      <div class="text-muted"><?= date('Y-m-d H:i') ?></div>
    </div>
  </div>
  <hr>
  <div class="row g-4">
    <div class="col-md-6">
      <div><span class="text-muted">الصنف:</span> <?= esc($r['name']) ?></div>
      <div><span class="text-muted">الكمية:</span> <?= esc($r['quantity'].' '.$r['unit']) ?></div>
      <div><span class="text-muted">السعر:</span> <?= number_format((float)$r['price'],2) ?> ريال</div>
      <div><span class="text-muted">الدافع:</span> <?= esc($r['payer_name']) ?></div>
      <div><span class="text-muted">مصدر الدفع:</span> <?= esc($r['payment_source']) ?></div>
      <div><span class="text-muted">التاريخ:</span> <?= esc($r['created_at']) ?></div>
    </div>
    <div class="col-md-6 text-end">
      <?php if($r['product_image']): ?>
        <div class="mb-3">
          <img src="uploads/<?= esc($r['product_image']) ?>" width="150" class="rounded border">
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
  var content = document.querySelector('.print-area').outerHTML;
  var printWindow = window.open('', '', 'width=900,height=700');
  printWindow.document.write(`
    <html>
      <head>
        <title>طباعة الفاتورة</title>
        <link rel="stylesheet" href="assets/bootstrap.min.css">
        <style>
          body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
          .print-area { padding: 30px; }
          img { max-width: 100%; border-radius: 8px; border: 1px solid #eee; }
        </style>
      </head>
      <body>${content}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}
</script>
