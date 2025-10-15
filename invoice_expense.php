<?php 
require __DIR__.'/partials/header.php'; 

$expenseId = (int)($_GET['id'] ?? 0);
$expenseStmt = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
$expenseStmt->execute([$expenseId]);
$expense = $expenseStmt->fetch();

if (!$expense) { 
    echo "<div class='alert alert-warning'>المصروف غير موجود</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}

// نسبة الضريبة
$vatRate = ($expense['has_vat'] == 1) ? 0.15 : 0.00;

// صورة الفاتورة (إن وجدت)
$invoiceImage = $expense['expense_file'] ?? null;
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
</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> طباعة</button>
</div>

<div class="print-area">
  <!-- شعار + عنوان + رقم تسلسلي -->
  <div class="d-flex flex-column align-items-center mb-3">
    <img src="assets/logo.png" class="logo mb-1" alt="Logo" style="width:150px; height:auto;">
    <h2 style="font-weight:bold; color:#000; margin:0;">فاتورة مصروفات</h2>
    <div class="invoice-serial">الرقم التسلسلي: <?= esc($expense['invoice_serial']) ?></div>
  </div>

  <div class="invoice-header">
    <div class="text-end invoice-info" style="flex:1">
      <div><strong>رقم الفاتورة:</strong> <?= esc($asset['bill_number']) ?></div>
      <div><strong>الدافع:</strong> <?= esc($expense['payer_name']) ?></div>
      <div><strong>مصدر الدفع:</strong> <?= esc($expense['payment_source']) ?></div>
      <div>
        <strong>التاريخ:</strong>
        <input type="date" id="invoiceDate" value="<?= date('Y-m-d', strtotime($expense['created_at'])) ?>" data-expense-id="<?= $expenseId ?>" style="border:1px solid #ccc; border-radius:4px; padding:2px 6px;">
        <span id="invoiceDateText" style="display:none; font-weight:bold;"><?= date('Y-m-d', strtotime($expense['created_at'])) ?></span>
      </div>
    </div>

    <?php if($invoiceImage): ?>
      <a href="uploads/<?= esc($invoiceImage) ?>" target="_blank">
        <img src="uploads/<?= esc($invoiceImage) ?>" alt="Expense File" class="invoice-image">
      </a>
    <?php endif; ?>
  </div>

  <!-- جدول المصروف -->
  <table id="invoiceTable">
    <thead>
      <tr>
        <th>المصروفات</th>
        <th>نوع المصروف</th>
        <th>بيان المصروف</th>
        <th>الاجمالي</th>
        <th>الضريبة</th>
        <th>الإجمالي بعد الضريبة</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $subtotal = $expense['expense_amount'];
        $vat = $subtotal * $vatRate;
        $total = $subtotal + $vat;
      ?>
      <tr data-amount="<?= $expense['expense_amount'] ?>" data-total="<?= $expense['total_amount'] ?>">
        <td><?= esc($expense['main_expense']) ?></td>
        <td><?= esc($expense['sub_expense']) ?></td>
        <td><?= esc($expense['expense_desc']) ?></td>
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
      <select id="vatRate" data-expense-id="<?= $expenseId ?>">
        <option value="0" <?= $vatRate == 0 ? 'selected' : '' ?>>0%</option>
        <option value="0.15" <?= $vatRate == 0.15 ? 'selected' : '' ?>>15%</option>
      </select>
      <span id="vatRateText"><?= $vatRate == 0 ? '0%' : '15%' ?></span>
    </div>
    <div><strong>المجموع:</strong> <span id="totalNoVat"><?= number_format($subtotal,2) ?></span> ريال</div>
    <div id="vatRow" <?= $vatRate == 0 ? 'style="display:none;"' : '' ?>><strong>الضريبة:</strong> <span id="vatValue"><?= number_format($expense['vat_value'],2) ?></span> ريال</div>
    <div id="grandRow" <?= $vatRate == 0 ? 'style="display:none;"' : '' ?>><strong>الإجمالي بعد الضريبة:</strong> <span id="grandTotal"><?= number_format($expense['total_amount'],2) ?></span> ريال</div>
  </div>
</div>

<script>
function recalcTotals(saveToDB = false) {
  const vatRateEl = document.getElementById('vatRate');
  const vatRate = parseFloat(vatRateEl.value);

  let totalBeforeVat = 0;
  let totalVat = 0;
  let totalAfterVat = 0;

  document.querySelectorAll('#invoiceTable tbody tr').forEach(tr => {
    const amount = parseFloat(tr.dataset.amount) || 0;       // قبل الضريبة
    const totalFromDB = parseFloat(tr.dataset.total) || 0;   // بعد الضريبة من DB
    const vatCell = tr.querySelector('.vat');
    const totalCell = tr.querySelector('.total');

    // 🧾 حالة الضريبة = 0%
    if (vatRate === 0) {
      tr.querySelector('td:nth-child(4)').textContent = totalFromDB.toFixed(2) + ' ريال'; // قبل الضريبة = بعد الضريبة
      vatCell.textContent = '—'; // إخفاء القيمة (شرطة فقط أو ممكن تسيبها فاضية '')
      totalCell.textContent = totalFromDB.toFixed(2) + ' ريال';

      totalBeforeVat += totalFromDB;
      totalAfterVat += totalFromDB;
    }

    // 💰 حالة الضريبة = 15%
    else {
      const vatValue = totalFromDB - amount;

      tr.querySelector('td:nth-child(4)').textContent = amount.toFixed(2) + ' ريال';
      vatCell.textContent = vatValue.toFixed(2) + ' ريال';
      totalCell.textContent = totalFromDB.toFixed(2) + ' ريال';

      totalBeforeVat += amount;
      totalVat += vatValue;
      totalAfterVat += totalFromDB;
    }
  });

  // ✅ تحديث الملخص
  document.getElementById('totalNoVat').textContent = totalBeforeVat.toFixed(2);
  document.getElementById('vatValue').textContent = totalVat.toFixed(2);
  document.getElementById('grandTotal').textContent = totalAfterVat.toFixed(2);

  // 🎯 إظهار / إخفاء صفوف الملخص حسب الضريبة
  const totalNoVatRow = document.getElementById('totalNoVat').closest('tr');
  const vatRow = document.getElementById('vatRow');
  const grandRow = document.getElementById('grandRow');

  if (vatRate === 0) {
    totalNoVatRow.style.display = 'none';
    vatRow.style.display = 'none';
    grandRow.style.display = 'table-row';
  } else {
    totalNoVatRow.style.display = 'table-row';
    vatRow.style.display = 'table-row';
    grandRow.style.display = 'table-row';
  }
}

// ✅ تحديث تلقائي عند التغيير أو تحميل الصفحة
document.getElementById('vatRate').addEventListener('change', () => recalcTotals(true));
window.addEventListener('DOMContentLoaded', () => recalcTotals(false));


const dateInput = document.getElementById('invoiceDate');
const dateText = document.getElementById('invoiceDateText');
dateInput.addEventListener('change', function() {
    const newDate = this.value;
    dateText.textContent = newDate;
    fetch('update_expense_date', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${this.dataset.expenseId}&date=${newDate}`
    }).then(res => res.text()).then(console.log).catch(console.error);
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
