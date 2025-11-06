<?php 
require __DIR__.'/partials/header.php'; 
require_permission('assets.print');

$assetId = (int)($_GET['id'] ?? 0);
$assetStmt = $pdo->prepare("SELECT * FROM assets WHERE id=?");
$assetStmt->execute([$assetId]);
$asset = $assetStmt->fetch();

if (!$asset) { 
    echo "<div class='alert alert-warning'>الأصل غير موجود</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}

// نسبة الضريبة
$vatRate = ($asset['has_vat'] == 1) ? 0.15 : 0.00;

// صورة الفاتورة (إن وجدت)
$invoiceImage = $asset['image'] ?? null;
?>

<style>
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; }
  select#vatRate { display: none !important; }
  #vatRateText { display: inline !important; }
  #invoiceDate { display: none !important; }
  #invoiceDateText { display: inline !important; }
}

.print-area {
  max-width: 900px; 
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

.invoice-image {
  max-width: 100%;
  margin: 15px 0;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 1px 1px 5px rgba(0,0,0,0.1);
}

select#vatRate {
  display: inline-block;
  width: auto;
  margin-right: 10px;
  border-radius: 4px;
  padding: 4px 8px;
}

#vatRateText { 
  display: none; 
  font-weight: bold;
}


.print-area {
  max-width: 900px;
  margin: auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 8px;
  background: #fff;
  direction: rtl;
  text-align: right;
  overflow-x: auto; /* مهم للجداول على الموبايل */
}

/* الجدول الأساسي */
.print-area table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  min-width: 800px; /* يمنع تكدس الأعمدة في الموبايل */
}

.print-area table th,
.print-area table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
  font-size: 14px;
  word-wrap: break-word;
}

.print-area table th {
  background-color: #f8f9fa;
  font-weight: bold;
}

/* الرأس */
.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-direction: row-reverse;
  flex-wrap: wrap; /* يجعل المحتوى ينزل تحت بعض في الموبايل */
  gap: 15px;
}

.invoice-info {
  flex: 1;
  min-width: 250px;
  text-align: right;
  line-height: 1.8;
}

.invoice-image {
  width: 150px;
  height: auto;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-shadow: 0 0 6px rgba(0,0,0,0.1);
  cursor: pointer;
  flex-shrink: 0;
}

/* الشعار والعنوان */
.logo {
  width: 120px;
  height: auto;
  margin-bottom: 10px;
}

.d-flex.flex-column.align-items-center.mb-3 h2 {
  font-size: 1.6rem;
}

/* الملخص */
.invoice-summary {
  margin-top: 20px;
  text-align: left;
  line-height: 1.8;
  font-size: 15px;
}

.invoice-summary div {
  margin-bottom: 5px;
}

select#vatRate {
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
}

/* تحسين الأزرار */
.btn-orange {
  background-color: #ff7f50;
  border: none;
  color: #fff;
  padding: 8px 16px;
  border-radius: 6px;
  transition: background 0.3s;
}

.btn-orange:hover {
  background-color: #ff6a33;
}

/* ============================= */
/* 📱 تحسين العرض على الشاشات الصغيرة */
/* ============================= */
@media (max-width: 768px) {
  .print-area {
    padding: 12px;
    border: none;
  }

  .invoice-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .invoice-info {
    text-align: center;
  }

  .invoice-image {
    width: 100%;
    max-width: 250px;
    margin: 10px auto;
  }

  table {
    font-size: 12px;
  }

  .invoice-summary {
    text-align: center;
  }

  .invoice-summary div {
    font-size: 14px;
  }

  .d-print-none.mb-3 {
    text-align: center;
  }

  .logo {
    width: 100px;
  }
}

/* ============================= */
/* 🖨️ تحسين المظهر عند الطباعة */
/* ============================= */
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; }
  select#vatRate, #invoiceDate { display: none !important; }
  #vatRateText, #invoiceDateText { display: inline !important; }
  .btn-orange { display: none; }
  table, th, td { font-size: 10px; padding: 3px; }
}

</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> طباعة</button>
</div>

<div class="print-area">
  <!-- شعار + عنوان + رقم تسلسلي -->
  <div class="d-flex flex-column align-items-center mb-3">
    <img src="assets/logo.png" class="logo mb-1" alt="Logo" style="width:150px; height:auto;">
    <h2 style="font-weight:bold; color:#000; margin:0;">فاتورة أصل ثابت</h2>
    <div class="invoice-serial">الرقم التسلسلي: <?= esc($asset['invoice_serial']) ?></div>
  </div>

  <div class="invoice-header">
    <div class="text-end invoice-info" style="flex:1">
      <div><strong>رقم الفاتورة:</strong> <?= esc($asset['bill_number']) ?></div>
      <div><strong>الدافع:</strong> <?= esc($asset['payer_name']) ?></div>
      <div><strong>مصدر الدفع:</strong> <?= esc($asset['payment_source']) ?></div>
      <div>
        <strong>التاريخ:</strong>
        <input type="date" id="invoiceDate" value="<?= date('Y-m-d', strtotime($asset['created_at'])) ?>" data-asset-id="<?= $assetId ?>" style="border:1px solid #ccc; border-radius:4px; padding:2px 6px;">
        <span id="invoiceDateText" style="display:none; font-weight:bold;"><?= date('Y-m-d', strtotime($asset['created_at'])) ?></span>
      </div>
    </div>

    <?php if($invoiceImage): ?>
      <a href="uploads/<?= esc($invoiceImage) ?>" target="_blank">
        <img src="uploads/<?= esc($invoiceImage) ?>" alt="Asset File" class="invoice-image">
      </a>
    <?php endif; ?>
  </div>

  <!-- جدول الأصل -->
  <table id="invoiceTable">
    <thead>
      <tr>
        <th>اسم الأصل</th>
        <th>النوع</th>
        <th>الكمية</th>
        <th>السعر</th>
        <th>الاجمالي</th>
        <th>الضريبة</th>
        <th>الإجمالي بعد الضريبة</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $subtotal = $asset['price'] * $asset['quantity'];
        $vat = $subtotal * $vatRate;
        $total = $subtotal + $vat;
      ?>
      <tr data-amount="<?= $subtotal ?>" data-price="<?= $asset['price'] ?>" data-total="<?= $asset['total_amount'] ?>">
        <td><?= esc($asset['name']) ?></td>
        <td><?= esc($asset['type']) ?></td>
        <td><?= esc($asset['quantity']) ?></td>
        <?php if($item['vat_value'] == 0){

            $pricewithvat = $asset['price'] + ($asset['price'] * 0.15);

        ?>
         <td><?= number_format($pricewithvat,7) ?> ريال</td>
        <?php  }else{ ?>
            <td><?= number_format($asset['price'],7) ?> ريال</td>
        <?php } ?>
        <td><?= number_format($subtotal,7) ?> ريال</td>
        <td class="vat"><?= number_format($vat,7) ?> ريال</td>
        <td class="total"><?= number_format($total,7) ?> ريال</td>
      </tr>
    </tbody>
  </table>

  <!-- الملخص -->
  <div class="invoice-summary">
    <div>
      <strong>نسبة الضريبة:</strong>
      <select id="vatRate" data-asset-id="<?= $assetId ?>">
        <option value="0" <?= $vatRate == 0 ? 'selected' : '' ?>>0%</option>
        <option value="0.15" <?= $vatRate == 0.15 ? 'selected' : '' ?>>15%</option>
      </select>
      <span id="vatRateText"><?= $vatRate == 0 ? '0%' : '15%' ?></span>
    </div>
    <div><strong>المجموع:</strong> <span id="totalNoVat"><?= number_format($subtotal,2) ?></span> ريال</div>
    <div id="vatRow" <?= $vatRate == 0 ? 'style="display:none;"' : '' ?>><strong>الضريبة:</strong> <span id="vatValue"><?= number_format($asset['vat_value'],2) ?></span> ريال</div>
    <div id="grandRow" <?= $vatRate == 0 ? 'style="display:none;"' : '' ?>><strong>الإجمالي بعد الضريبة:</strong> <span id="grandTotal"><?= number_format($asset['total_amount'],2) ?></span> ريال</div>
  </div>
</div>

<script>
function recalcTotals(saveToDB = false) {
  const vatRateEl = document.getElementById('vatRate');
  const vatTextEl = document.getElementById('vatRateText');
  const vatRate = parseFloat(vatRateEl.value);
  const assetId = vatRateEl.dataset.assetId;

  vatTextEl.textContent = vatRate === 0 ? '0%' : '15%';

  const tr = document.querySelector('#invoiceTable tbody tr');
  const subtotal = parseFloat(tr.dataset.amount) || 0;
  const totalFromDB = parseFloat(tr.dataset.total) || subtotal; // اجمالي من قاعدة البيانات

  const price = parseFloat(tr.dataset.price) || 0;

  if (vatRate === 0) {
    // الصفر: استخدم total_amount من قاعدة البيانات لكل القيم
    tr.querySelector('td:nth-child(5)').textContent = totalFromDB.toFixed(2) + ' ريال'; // الإجمالي قبل الضريبة
    tr.querySelector('.vat').textContent = '0.00 ريال';                                 // الضريبة
    tr.querySelector('.total').textContent = totalFromDB.toFixed(2) + ' ريال';        // الإجمالي بعد الضريبة

    const priceWithvatValue = price + (price * 0.15);
    tr.querySelector('td:nth-child(4)').textContent = priceWithvatValue.toFixed(7) + ' ريال';

    // الملخص
    document.getElementById('totalNoVat').textContent = totalFromDB.toFixed(2);
    document.getElementById('totalNoVat').parentElement.style.display = 'block'; // يمكن اخفاؤه حسب التصميم
    document.getElementById('vatRow').style.display = 'none';
    document.getElementById('grandRow').style.display = 'none';
  } else {
    // 15%: حساب القيم الطبيعية
    const vat = subtotal * vatRate;
    const total = subtotal + vat;

    tr.querySelector('td:nth-child(5)').textContent = subtotal.toFixed(2) + ' ريال';
    tr.querySelector('.vat').textContent = vat.toFixed(2) + ' ريال';
    tr.querySelector('.total').textContent = total.toFixed(2) + ' ريال';
    tr.querySelector('td:nth-child(4)').textContent = price.toFixed(7) + ' ريال';

    // الملخص
    document.getElementById('totalNoVat').textContent = subtotal.toFixed(2);
    document.getElementById('vatValue').textContent = vat.toFixed(2);
    document.getElementById('grandTotal').textContent = total.toFixed(2);

    document.getElementById('totalNoVat').parentElement.style.display = 'block';
    document.getElementById('vatRow').style.display = 'block';
    document.getElementById('grandRow').style.display = 'block';
  }

  if (saveToDB) {
    fetch('update_asset_vat', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${assetId}&vat_value=${vatRate > 0 ? (subtotal*vatRate) : 0}&total_amount=${vatRate > 0 ? (subtotal*(1+vatRate)) : totalFromDB}&has_vat=${vatRate > 0 ? 1 : 0}`
    })
    .then(res => res.text())
    .then(console.log)
    .catch(console.error);
  }
}


document.getElementById('vatRate').addEventListener('change', () => recalcTotals(true));
window.addEventListener('DOMContentLoaded', () => recalcTotals(false));


const dateInput = document.getElementById('invoiceDate');
const dateText = document.getElementById('invoiceDateText');
dateInput.addEventListener('change', function() {
    const newDate = this.value;
    dateText.textContent = newDate;
    fetch('update_asset_date', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${this.dataset.assetId}&date=${newDate}`
    }).then(res => res.text()).then(console.log).catch(console.error);
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
