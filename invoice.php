<?php 
require __DIR__.'/partials/header.php'; 
require_permission('purchases.print');

$purchaseId = (int)($_GET['id'] ?? 0);
$purchaseStmt = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
$purchaseStmt->execute([$purchaseId]);
$purchase = $purchaseStmt->fetch();

if (!$purchase) { 
    echo "<div class='alert alert-warning'>المشتريات غير موجودة</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}

$orderId = $purchase['order_id'] ?? null;
if ($orderId) {
    $orderStmt = $pdo->prepare("SELECT * FROM orders_purchases WHERE id=?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();

    $itemsStmt = $pdo->prepare("SELECT * FROM purchases WHERE order_id=?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();
} else {
    echo "<div class='alert alert-warning'>الفاتورة غير موجودة</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}

  // قيم الإجماليات من DB
  $subtotalAll = $order['total'];      // المجموع قبل الضريبة
  $totalVat    = $order['vat'];        // الضريبة
  $grandTotal  = $order['all_total'];  // الإجمالي بعد الضريبة


// نسبة الضريبة حسب قاعدة البيانات
$vatRate = ($order['vat'] > 0) ? 0.15 : 0.00;

// صورة الفاتورة العامة (إن وجدت)
$invoiceImage = $order['invoice_image'] ?? null;
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
  text-align: right;
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
.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-direction: row-reverse; /* RTL */
}

.invoice-image {
  width: 150px;           /* عرض ثابت */
  height: auto;           /* يحافظ على النسبة */
  cursor: pointer;        /* يظهر أنها قابلة للنقر */
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 1px 1px 5px rgba(0,0,0,0.1);
  margin-left: 15px;      /* مسافة بين البيانات والصورة */
  flex-shrink: 0;         /* لا يتقلص */
}
.invoice-summary {
    margin-top: 20px;
    text-align: left; /* بدل right */
    line-height: 1.8;
}

@media print {
  body { font-size: 10px; }
  table { page-break-inside: auto; }
  tr    { page-break-inside: avoid; page-break-after: auto; }
  th, td { padding: 3px; }
}
table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* مهم */
}

th, td {
  border: 1px solid #ddd;
  padding: 4px;
  text-align: center;
  word-wrap: break-word; /* لتقسيم النصوص الطويلة */
}
.badge-unit {
  display: inline-block;
  background-color: #f0f0f0;
  color: #333;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.9em;
}

/* صف الصنف المميز */
.highlighted-row {
  background-color: #fff3cd !important; /* لون أصفر فاتح */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 0 10px rgba(255, 193, 7, 0.6);
  position: relative;
  z-index: 1;
}

/* حركة البربشة */
.blinking {
  transform: scale(1.03);
  box-shadow: 0 0 15px rgba(255, 193, 7, 0.9);
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

/* الصف المميز */
.highlighted-row {
  background-color: #fff3cd !important;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 0 10px rgba(255, 193, 7, 0.6);
}

.blinking {
  transform: scale(1.03);
  box-shadow: 0 0 15px rgba(255, 193, 7, 0.9);
}


</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> طباعة</button>
</div>

<div class="print-area">
  <!-- شعار + عنوان + رقم تسلسلي -->
<div class="d-flex flex-column align-items-center mb-3">
  <img src="assets/logo.png" class="logo mb-1" alt="Logo" style="width:150px; height:auto;">
  <h2 style="font-weight:bold; color:#000; margin:0;">فاتورة مشتريات</h2>
  <div class="invoice-serial">الرقم التسلسلي: <?= esc($order['invoice_serial'] ?? $order['invoice_number']) ?></div>
</div>

<div class="invoice-header">
  <div class="text-end invoice-info" style="flex:1">
    <div><strong>رقم فاتورة المورد:</strong> <?= esc($order['bill_number']) ?></div>
    <div><strong>الرقم الضريبي للمورد:</strong> <?= esc($order['tax_number']) ?></div>
    <div><strong>المورد:</strong> <?= esc($order['supplier_name']) ?></div>
    <div><strong>رقم الفاتورة:</strong> <?= esc($order['invoice_number']) ?></div>
    <div><strong>الدافع:</strong> <?= esc($purchase['payer_name']) ?></div>
    <div><strong>مصدر الدفع:</strong> <?= esc($purchase['payment_source']) ?></div>
    <div>
      <strong>التاريخ:</strong>
      <input type="date" id="invoiceDate" value="<?= date('Y-m-d', strtotime($order['created_at'])) ?>" data-order-id="<?= $orderId ?>" style="border:1px solid #ccc; border-radius:4px; padding:2px 6px;">
      <span id="invoiceDateText" style="display:none; font-weight:bold;"><?= date('Y-m-d', strtotime($order['created_at'])) ?></span>
    </div>
  </div>

  <?php if($invoiceImage): ?>
    <a href="uploads/<?= esc($invoiceImage) ?>" target="_blank">
      <img src="uploads/<?= esc($invoiceImage) ?>" alt="Invoice Image" class="invoice-image">
    </a>
  <?php endif; ?>
</div>


  <!-- جدول الأصناف -->
  <table id="invoiceTable">
    <thead>
      <tr>
        <th>البيان</th>
        <th>نوع الوحدة</th>
        <th>الكمية</th>
        <th>السعر</th>
        <th>الكميات بالوحدة</th>
        <th>السعر الافرادي</th>
        <th>اجمالي الكميات</th>
        <th>الاجمالي</th>
        <th>الضريبة</th>
        <th>الاجمالي بعد الضريبه</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $item): 
        $subtotal = $item['quantity'] * $item['price'];
        $vat = $subtotal * $vatRate;
        $total = $subtotal + $vat;
      ?>
      <tr data-qty="<?= $item['quantity'] ?>" data-price="<?= $item['price'] ?>" data-total_price="<?= $item['total_price'] ?>" data-unit-total="<?= $item['unit_total'] ?>" data-unit-all-total="<?= $item['unit_all_total'] ?>">
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['unit']) ?></td>
        <td>
          
            <?= htmlspecialchars($item['total_packages']) ?>
            <?php if (!empty($item['package'])): ?>
              × <?= htmlspecialchars($item['package']) ?>
            <?php endif; ?>
          
        </td>
        <?php if($item['unit_vat'] == 0){

            $totalpricewithvat = $item['total_price'] + ($item['total_price'] * 0.15);

        ?>
         <td><?= number_format($totalpricewithvat,5) ?> ريال</td>
        <?php  }else{ ?>
            <td><?= number_format($item['total_price'],5) ?> ريال</td>
        <?php } ?>
        <td><?= esc($item['single_package']) ?></td>

        <?php if($item['unit_vat'] == 0){

            $pricewithvat = $item['price'] + ($item['price'] * 0.15);

        ?>
         <td><?= number_format($pricewithvat,5) ?> ريال</td>
        <?php  }else{ ?>
            <td><?= number_format($item['price'],5) ?> ريال</td>
        <?php } ?>
        <td><?= esc($item['quantity']) ?></td>
        <td><?= number_format($item['unit_total'],3) ?> ريال</td>
        <td class="vat"><?= number_format($item['unit_vat'],5) ?> ريال</td>
        <td class="total"><?= number_format($item['unit_all_total'],5) ?> ريال</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- الملخص -->
  <div class="invoice-summary">
    <div>
      <strong>نسبة الضريبة:</strong>
      <select id="vatRate" data-order-id="<?= $orderId ?>">
        <option value="0" <?= $vatRate == 0 ? 'selected' : '' ?>>0%</option>
        <option value="0.15" <?= $vatRate == 0.15 ? 'selected' : '' ?>>15%</option>
      </select>
      <span id="vatRateText"><?= $vatRate == 0 ? '0%' : '15%' ?></span>
    </div>
    <div><strong>المجموع:</strong> <span id="totalNoVat"><?= number_format($subtotalAll,2) ?></span> ريال</div>
    <div id="vatRow" style="display: <?= $vatRate==0?'none':'block' ?>;"><strong>الضريبة:</strong> <span id="vatValue"><?= number_format($totalVat,2) ?></span> ريال</div>
    <div id="grandRow" style="display: <?= $vatRate==0?'none':'block' ?>;"><strong>الإجمالي بعد الضريبة:</strong> <span id="grandTotal"><?= number_format($grandTotal,2) ?></span> ريال</div>
  </div>
</div>

<script>
function recalcTotals(saveToDB = false) {
  const vatRateEl = document.getElementById('vatRate');
  const vatTextEl = document.getElementById('vatRateText');
  const vatRate = parseFloat(vatRateEl.value);
  const orderId = vatRateEl.dataset.orderId;

  vatTextEl.textContent = vatRate === 0 ? '0%' : '15%';

  let subtotalAll = 0;
  let grandTotal = 0;
  let totalVat = 0;

  document.querySelectorAll('#invoiceTable tbody tr').forEach(tr => {
    const price = parseFloat(tr.dataset.price || tr.querySelector('td:nth-child(6)').textContent.replace(/[^\d.-]/g, '')) || 0;
    const totalPrice = parseFloat(tr.dataset.total_price || tr.querySelector('td:nth-child(4)').textContent.replace(/[^\d.-]/g, '')) || 0;
    const unitTotal = parseFloat(tr.dataset.unitTotal || tr.querySelector('td:nth-child(8)').textContent.replace(/[^\d.-]/g, '')) || 0;
    const unitAllTotal = parseFloat(tr.dataset.unitAllTotal || tr.querySelector('td:nth-child(10)').textContent.replace(/[^\d.-]/g, '')) || 0;

    if(vatRate === 0){

      const priceWithvatValue = price + (price * 0.15);
      const pricetotalWithvatValue = totalPrice + (totalPrice * 0.15);
      // حالة صفر: العمود قبل الضريبة = unit_all_total
      tr.querySelector('td:nth-child(8)').textContent = unitAllTotal.toFixed(5) + ' ريال';
      tr.querySelector('.vat').textContent = '0.00 ريال';
      tr.querySelector('.total').textContent = unitAllTotal.toFixed(5) + ' ريال';

      tr.querySelector('td:nth-child(6)').textContent = priceWithvatValue.toFixed(5) + ' ريال';
      tr.querySelector('td:nth-child(4)').textContent = pricetotalWithvatValue.toFixed(5) + ' ريال';

      subtotalAll += unitAllTotal;
      totalVat += 0;
      grandTotal += unitAllTotal;
    } else {
      // حالة 15%
      tr.querySelector('td:nth-child(8)').textContent = unitTotal.toFixed(5) + ' ريال';
      const vatValue = unitTotal * vatRate;
      const totalWithVat = unitTotal + vatValue;

      tr.querySelector('.vat').textContent = vatValue.toFixed(5) + ' ريال';
      tr.querySelector('.total').textContent = totalWithVat.toFixed(5) + ' ريال';

      tr.querySelector('td:nth-child(6)').textContent = price.toFixed(5) + ' ريال';
      tr.querySelector('td:nth-child(4)').textContent = totalPrice.toFixed(5) + ' ريال';

      subtotalAll += unitTotal;
      totalVat += vatValue;
      grandTotal += totalWithVat;
    }
  });

  // تحديث الملخص
  const totalNoVatEl = document.getElementById('totalNoVat');
  const vatValueEl = document.getElementById('vatValue');
  const grandTotalEl = document.getElementById('grandTotal');

  if(vatRate === 0){
    // إظهار سطر واحد فقط مع الإجمالي بعد الضريبة
    totalNoVatEl.textContent = grandTotal.toLocaleString(undefined, {minimumFractionDigits:2});
    vatValueEl.textContent = '';
    grandTotalEl.textContent = grandTotal.toLocaleString(undefined, {minimumFractionDigits:2});

    document.getElementById('vatRow').style.display = 'none';
    document.getElementById('grandRow').style.display = 'none';
  } else {
    totalNoVatEl.textContent = subtotalAll.toLocaleString(undefined, {minimumFractionDigits:2});
    vatValueEl.textContent = totalVat.toLocaleString(undefined, {minimumFractionDigits:2});
    grandTotalEl.textContent = grandTotal.toLocaleString(undefined, {minimumFractionDigits:2});

    document.getElementById('vatRow').style.display = 'block';
    document.getElementById('grandRow').style.display = 'block';
  }

  if(saveToDB){
    fetch('update_vat', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `order_id=${orderId}&vat=${totalVat}&all_total=${grandTotal}&vat_rate=${vatRate}`
    }).then(res=>res.text()).then(console.log).catch(console.error);
  }
}

document.getElementById('vatRate').addEventListener('change', () => recalcTotals(true));
window.addEventListener('DOMContentLoaded', () => recalcTotals(false));

const dateInput = document.getElementById('invoiceDate');
const dateText = document.getElementById('invoiceDateText');
dateInput.addEventListener('change', function() {
    const newDate = this.value;
    dateText.textContent = newDate;
    fetch('update_invoice_date', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `order_id=${this.dataset.orderId}&date=${newDate}`
    }).then(res => res.text()).then(console.log).catch(console.error);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  const highlightName = urlParams.get('highlight');

  if (!highlightName) return; // لو مفيش هايلايت خلاص

  // نجيب كل الصفوف
  const rows = document.querySelectorAll('#invoiceTable tbody tr');
  if (rows.length <= 1) return; // لو مفيش غير صف واحد ما نعملش حاجة

  rows.forEach(tr => {
    const cellText = tr.cells[0].innerText.trim().replace(/\s+/g, ''); // العمود الأول (الاسم)
    const targetName = highlightName.trim().replace(/\s+/g, '');
    if (cellText === targetName) {
      tr.classList.add('highlighted-row');

      // نحط تركيز عليه في الشاشة
      tr.scrollIntoView({ behavior: 'smooth', block: 'center' });

      // نعمل فلاش بسيط متكرر
      let blinkCount = 0;
      const blinkInterval = setInterval(() => {
        tr.classList.toggle('blinking');
        blinkCount++;
        if (blinkCount > 6) { // 3 مرات تقريبًا
          clearInterval(blinkInterval);
          tr.classList.remove('blinking');
        }
      }, 300);

      // لما المستخدم يمر عليه بالماوس يرجع طبيعي
      tr.addEventListener('mouseenter', () => {
        tr.classList.remove('highlighted-row', 'blinking');
      });
    }
  });
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
