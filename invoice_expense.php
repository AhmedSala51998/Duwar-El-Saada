<?php 
require __DIR__.'/partials/header.php'; 
require_permission('expenses.print');

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
  /* ✅ ضمان ظهور الجدول بالكامل وعدم القص */
  html, body {
    width: 100%;
    overflow: visible !important;
    -webkit-print-color-adjust: exact;
  }

  .print-area {
    width: 100%;
    max-width: none !important;
    overflow: visible !important;
    zoom: 0.85; /* تقلل الحجم شوية ليظهر الجدول كامل في عرض الصفحة */
  }

  table {
    width: 100%;
    min-width: auto !important;
    table-layout: auto !important;
    overflow: visible !important;
  }

  th, td {
    white-space: nowrap; /* يمنع التكسير الغريب أثناء الطباعة */
    word-wrap: normal;
  }

  /* ✅ السماح للجدول أن يمتد لعدة صفحات */
  tr, td, th {
    page-break-inside: avoid;
  }

  /* إخفاء أي تمرير أفقي أثناء الطباعة */
  .print-area::-webkit-scrollbar {
    display: none;
  }
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
  const vatTextEl = document.getElementById('vatRateText');
  const vatRate = parseFloat(vatRateEl.value);
  const expenseId = vatRateEl.dataset.expenseId;

  vatTextEl.textContent = vatRate === 0 ? '0%' : '15%';

  const tr = document.querySelector('#invoiceTable tbody tr');
  const expenseAmount = parseFloat(tr.dataset.amount) || 0;       // $expense['expense_amount']
  const totalAmountDB = parseFloat(tr.dataset.total) || 0;        // $expense['total_amount']

  if (vatRate === 0) {
    // ✅ في حالة الصفر: استخدم total_amount من قاعدة البيانات
    tr.querySelector('td:nth-child(4)').textContent = totalAmountDB.toFixed(2) + ' ريال'; // الإجمالي قبل الضريبة
    tr.querySelector('.vat').textContent = '0.00 ريال';                                   // الضريبة
    tr.querySelector('.total').textContent = totalAmountDB.toFixed(2) + ' ريال';          // الإجمالي بعد الضريبة

    // ✅ الملخص
    document.getElementById('totalNoVat').textContent = totalAmountDB.toFixed(2);
    document.getElementById('totalNoVat').parentElement.style.display = 'block';
    document.getElementById('vatRow').style.display = 'none';
    document.getElementById('grandRow').style.display = 'none';
  } else {
    // ✅ في حالة 15%: استخدم expense_amount
    const vat = expenseAmount * vatRate;
    const total = expenseAmount + vat;

    tr.querySelector('td:nth-child(4)').textContent = expenseAmount.toFixed(2) + ' ريال'; // الإجمالي قبل الضريبة
    tr.querySelector('.vat').textContent = vat.toFixed(2) + ' ريال';
    tr.querySelector('.total').textContent = total.toFixed(2) + ' ريال';

    // ✅ الملخص
    document.getElementById('totalNoVat').textContent = expenseAmount.toFixed(2);
    document.getElementById('vatValue').textContent = vat.toFixed(2);
    document.getElementById('grandTotal').textContent = total.toFixed(2);

    document.getElementById('totalNoVat').parentElement.style.display = 'block';
    document.getElementById('vatRow').style.display = 'block';
    document.getElementById('grandRow').style.display = 'block';
  }

  // ✅ حفظ في قاعدة البيانات
  if (saveToDB) {
    fetch('update_expense_vat', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${expenseId}&vat_value=${vatRate > 0 ? (expenseAmount * vatRate) : 0}&total_amount=${vatRate > 0 ? (expenseAmount * (1 + vatRate)) : totalAmountDB}&has_vat=${vatRate > 0 ? 1 : 0}`
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
    fetch('update_expense_date', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${this.dataset.expenseId}&date=${newDate}`
    }).then(res => res.text()).then(console.log).catch(console.error);
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
