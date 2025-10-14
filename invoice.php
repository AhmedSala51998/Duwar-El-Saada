<?php 
require __DIR__.'/partials/header.php'; 

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
        <th>الصنف</th>
        <th>العبوة</th>
        <th>الكمية</th>
        <th>الوحدة</th>
        <th>السعر</th>
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
      <tr data-qty="<?= $item['quantity'] ?>" data-price="<?= $item['price'] ?>">
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['package']) ?></td>
        <td><?= esc($item['quantity'] . ' ' . $item['unit']) ?></td>
        <td><?= esc($item['unit']) ?></td>
        <td><?= number_format($item['price'],5) ?> ريال</td>
        <td><?= number_format($subtotal,3) ?> ريال</td>
        <td class="vat"><?= number_format($vat,5) ?> ريال</td>
        <td class="total"><?= number_format($total,5) ?> ريال</td>
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
    <div><strong>المجموع:</strong> <span id="totalNoVat">0.00</span> ريال</div>
    <div id="vatRow"><strong>الضريبة:</strong> <span id="vatValue">0.00</span> ريال</div>
    <div id="grandRow"><strong>الإجمالي بعد الضريبة:</strong> <span id="grandTotal">0.00</span> ريال</div>
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

  document.querySelectorAll('#invoiceTable tbody tr').forEach(tr => {
    const qty = parseFloat(tr.dataset.qty);
    const price = parseFloat(tr.dataset.price);
    const subtotal = qty * price;
    const vat = subtotal * vatRate;
    const total = subtotal + vat;

    tr.querySelector('.vat').textContent = vat.toLocaleString(undefined, {minimumFractionDigits:2}) + ' ريال';
    tr.querySelector('.total').textContent = total.toLocaleString(undefined, {minimumFractionDigits:2}) + ' ريال';

    subtotalAll += subtotal;
    grandTotal += total;
  });

  const vatValue = grandTotal - subtotalAll;

  document.getElementById('totalNoVat').textContent = subtotalAll.toLocaleString(undefined, {minimumFractionDigits:2});
  document.getElementById('vatValue').textContent = vatValue.toLocaleString(undefined, {minimumFractionDigits:2});
  document.getElementById('grandTotal').textContent = grandTotal.toLocaleString(undefined, {minimumFractionDigits:2});

  document.getElementById('vatRow').style.display = vatRate === 0 ? 'none' : 'block';
  document.getElementById('grandRow').style.display = vatRate === 0 ? 'none' : 'block';

  if (saveToDB) {
    fetch('update_vat', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `order_id=${orderId}&vat=${vatValue}&all_total=${grandTotal}`
    }).then(res => res.text()).then(console.log).catch(console.error);
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

<?php require __DIR__.'/partials/footer.php'; ?>
