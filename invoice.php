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
?>

<style>
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; }
  select#vatRate { display: none !important; }
  #vatRateText { display: inline !important; }
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

.invoice-info {
  text-align: right;
  line-height: 1.8;
  margin-top: 10px;
}

.invoice-summary {
  margin-top: 20px;
  text-align: right;
  line-height: 1.8;
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
</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> طباعة</button>
</div>

<div class="print-area">
  <div class="d-flex justify-content-between align-items-center mb-3" style="flex-direction: row-reverse;">
    <div class="text-end invoice-info">
      <div><strong>المورد:</strong> <?= esc($order['supplier_name']) ?></div>
      <div><strong>رقم الفاتورة:</strong> <?= esc($order['invoice_number']) ?></div>
      <div><strong>الدافع:</strong> <?= esc($purchase['payer_name']) ?></div>
      <div><strong>مصدر الدفع:</strong> <?= esc($purchase['payment_source']) ?></div>
      <div><strong>التاريخ:</strong> <?= esc($order['created_at']) ?></div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <h2>فاتورة مشتريات</h2>
      <img src="assets/logo.svg" class="logo" alt="Logo">
    </div>
  </div>

  <hr>

  <table id="invoiceTable">
    <thead>
      <tr>
        <th>الصنف</th>
        <th>الكمية</th>
        <th>الوحدة</th>
        <th>السعر</th>
        <!--<th>المجموع الفرعي</th>-->
        <th>الضريبة 15%</th>
        <th>الإجمالي بعد الضريبة</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $item): 
        $subtotal = $item['quantity'] * $item['price'];
        $vat = $subtotal * 0.15;
        $total = $subtotal + $vat;
      ?>
      <tr data-qty="<?= $item['quantity'] ?>" data-price="<?= $item['price'] ?>">
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['quantity']) ?></td>
        <td><?= esc($item['unit']) ?></td>
        <td><?= number_format($item['price'],2) ?> ريال</td>
        <!--<td class="subtotal"><?= number_format($subtotal,2) ?> ريال</td>-->
        <td class="vat"><?= number_format($vat,2) ?> ريال</td>
        <td class="total"><?= number_format($total,2) ?> ريال</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="invoice-summary">
    <div>
      <strong>نسبة الضريبة:</strong>
      <select id="vatRate">
        <option value="0">0%</option>
        <option value="0.15" selected>15%</option>
      </select>
      <span id="vatRateText">15%</span>
    </div>
    <div><strong>المجموع:</strong> <span id="totalNoVat">0.00</span> ريال</div>
    <div id="vatRow"><strong>الضريبة:</strong> <span id="vatValue">0.00</span> ريال</div>
    <div id="grandRow"><strong>الإجمالي بعد الضريبة:</strong> <span id="grandTotal">0.00</span> ريال</div>
  </div>
</div>

<script>
function recalcTotals() {
  const vatRateEl = document.getElementById('vatRate');
  const vatTextEl = document.getElementById('vatRateText');
  const vatRate = parseFloat(vatRateEl.value);
  vatTextEl.textContent = vatRate === 0 ? '0%' : '15%';

  let total = 0;
  document.querySelectorAll('#invoiceTable tbody tr').forEach(tr => {
    const qty = parseFloat(tr.dataset.qty);
    const price = parseFloat(tr.dataset.price);
    const subtotal = qty * price;
    const vat = subtotal * vatRate;
    const totalWithVat = subtotal + vat;

    tr.querySelector('.subtotal').textContent = subtotal.toLocaleString(undefined, {minimumFractionDigits:2}) + ' ريال';
    tr.querySelector('.vat').textContent = vat.toLocaleString(undefined, {minimumFractionDigits:2}) + ' ريال';
    tr.querySelector('.total').textContent = totalWithVat.toLocaleString(undefined, {minimumFractionDigits:2}) + ' ريال';
    total += subtotal;
  });

  const vatValue = total * vatRate;
  const grand = total + vatValue;
  
  document.getElementById('totalNoVat').textContent = total.toLocaleString(undefined, {minimumFractionDigits:2});
  document.getElementById('vatValue').textContent = vatValue.toLocaleString(undefined, {minimumFractionDigits:2});
  document.getElementById('grandTotal').textContent = grand.toLocaleString(undefined, {minimumFractionDigits:2});

  // إخفاء أو إظهار الصفوف حسب الضريبة
  document.getElementById('vatRow').style.display = vatRate === 0 ? 'none' : 'block';
  document.getElementById('grandRow').style.display = vatRate === 0 ? 'none' : 'block';
}

document.getElementById('vatRate').addEventListener('change', recalcTotals);
window.addEventListener('DOMContentLoaded', recalcTotals);
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
