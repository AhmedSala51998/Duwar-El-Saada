<?php 
require __DIR__.'/partials/header.php'; 
require_permission('custodies.print');

$custodyId = (int)($_GET['id'] ?? 0);
$custodyStmt = $pdo->prepare("SELECT * FROM custodies WHERE id = ?");
$custodyStmt->execute([$custodyId]);
$custody = $custodyStmt->fetch();

if (!$custody) { 
    echo "<div class='alert alert-warning'>العهدة غير موجودة</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}
?>

<style>
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; }
  #custodyDate { display: none !important; }
  #custodyDateText { display: inline !important; }
}

.print-area {
  max-width: 800px; 
  margin: auto; 
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  border: 1px solid #ccc; 
  padding: 20px; 
  border-radius: 8px; 
  background: #fff;
  direction: rtl;
  text-align: right;
}

.print-area table {
  width: 100%; 
  border-collapse: collapse; 
  margin-top: 15px;
}

.print-area table th, .print-area table td {
  border: 1px solid #ccc; 
  padding: 8px; 
  text-align: center;
}

.print-area table th {
  background-color: #f2f2f2;
}

.logo { width: 80px; }

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-direction: row-reverse;
  margin-bottom: 15px;
}

.invoice-info {
  text-align: right;
  line-height: 1.8;
}

.invoice-summary {
  margin-top: 20px;
  text-align: left;
  line-height: 1.8;
}

.invoice-serial {
  font-weight: bold;
  color: #000;
  font-size: 1.1em;
  margin-top: 5px;
}

/* ===== شاشة الموبايل ===== */
@media (max-width: 768px) {
  .print-area {
    padding: 10px;
    border: none;
    border-radius: 0;
    font-size: 14px;
  }

  .invoice-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .invoice-info {
    width: 100%;
    text-align: right;
    margin-bottom: 10px;
  }

  .invoice-image {
    width: 100%;
    max-width: 200px;
    margin-left: 0;
    margin-bottom: 10px;
  }

  table#invoiceTable {
    font-size: 12px;
  }

  table#invoiceTable th,
  table#invoiceTable td {
    padding: 6px 3px;
  }

  .invoice-summary {
    text-align: right;
    font-size: 13px;
  }

  .invoice-summary div {
    margin-bottom: 6px;
  }
}

</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange">
    <i class="bi bi-printer"></i> طباعة
  </button>
</div>

<div class="print-area">
  <!-- شعار + عنوان + رقم تسلسلي -->
  <div class="d-flex flex-column align-items-center mb-3">
    <img src="assets/logo.png" class="logo mb-1" alt="Logo" style="width:150px; height:auto;">
    <h2 style="font-weight:bold; color:#000; margin:0;">سند عهدة</h2>
    <div class="invoice-serial">الرقم التسلسلي: <?= esc($custody['invoice_serial']) ?></div>
  </div>

  <div class="invoice-header">
    <div class="invoice-info" style="flex:1">
      <div><strong>اسم المستلم:</strong> <?= esc($custody['person_name']) ?></div>
      <div>
        <strong>تاريخ الاستلام:</strong>
        <input 
          type="date" 
          id="custodyDate" 
          value="<?= esc($custody['taken_at']) ?>" 
          data-custody-id="<?= $custodyId ?>"
          style="border:1px solid #ccc; border-radius:4px; padding:2px 6px;"
        >
        <span id="custodyDateText" style="display:none; font-weight:bold;">
          <?= esc($custody['taken_at']) ?>
        </span>
      </div>
    </div>
  </div>

  <!-- جدول العهدة -->
  <table>
    <thead>
      <tr>
        <th>اسم الشخص</th>
        <th>المبلغ</th>
        <th>الملاحظات</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= esc($custody['person_name']) ?></td>
        <td><?= number_format($custody['main_amount'], 2) ?> ريال</td>
        <td><?= nl2br(esc($custody['notes'])) ?></td>
      </tr>
    </tbody>
  </table>

  <!-- الملخص -->
  <div class="invoice-summary">
    <div><strong>الإجمالي:</strong> <?= number_format($custody['main_amount'], 2) ?> ريال</div>
    <div><strong>تاريخ الإدخال:</strong> <?= esc($custody['created_at']) ?></div>
  </div>
</div>

<script>
// تحديث التاريخ عند التغيير
const dateInput = document.getElementById('custodyDate');
const dateText = document.getElementById('custodyDateText');
dateInput.addEventListener('change', function() {
    const newDate = this.value;
    dateText.textContent = newDate;

    fetch('update_custody_date', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${this.dataset.custodyId}&date=${newDate}`
    })
    .then(res => res.text())
    .then(console.log)
    .catch(console.error);
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
