<?php 
require __DIR__.'/partials/header.php'; 
require_permission('assets.print');

$assetId = (int)($_GET['id'] ?? 0);
$assetStmt = $pdo->prepare("
    SELECT a.*, b.branch_name AS branch_name
    FROM assets a
    LEFT JOIN branches b ON b.id = a.branch_id
    WHERE a.id = ?
");
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

function numberToArabicWords($number) {
    $fmt = new NumberFormatter("ar", NumberFormatter::SPELLOUT);
    $integerPart = floor($number);
    $fractionPart = round(($number - $integerPart) * 100);

    $words = $fmt->format($integerPart);

    if ($fractionPart > 0) {
        $fractionWords = $fmt->format($fractionPart);
        return "$words ريال و$fractionWords هللة";
    } else {
        return "$words ريال";
    }
}
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

/* ===== شاشة الموبايل ===== */
/* ======= فقط على الشاشات (وليس الطباعة) ======= */
@media screen and (max-width: 768px) {
  .table-responsive {
    overflow-x: auto !important;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch; /* تمرير سلس */
    border: none !important;
  }

  table#invoiceTable {
    width: 100%;
    min-width: 700px; /* علشان يقدر يعمل scroll لو ضاق العرض */
    border-collapse: collapse;
    font-size: 13px;
    table-layout: auto !important; /* يخلي الأعمدة تتسع تلقائياً حسب النص */
  }

  table#invoiceTable th,
  table#invoiceTable td {
    white-space: normal !important; /* يسمح بتكسر السطر */
    word-wrap: break-word !important; /* يمنع التداخل */
    word-break: break-word !important;
    text-align: center;
    vertical-align: middle;
    padding: 6px 4px;
  }

  table#invoiceTable th {
    background-color: #f8f9fa;
    font-weight: 600;
  }

  /* تأكد إن الجدول ما يخرجش عن حدود الصفحة */
  .print-area {
    overflow-x: hidden;
    padding: 10px;
  }
}


/* ======= عند الطباعة (اخفاء الاسكرول نهائيًا) ======= */
@media print {
  .table-responsive {
    overflow: visible !important;
  }

}

/* ======== الطباعة فقط ======== */
@media print {

  /* الجدول */
  .table-responsive {
    overflow: visible !important;
    box-shadow: none !important;
    border: none !important;
  }

  /* طباعة بلون النصوص الأسود فقط */
  @page {
    size: A4 portrait; /* يمكن تغييرها إلى landscape */
    margin: 10mm;
  }
}

/* ======== عرض الموبايل فقط ======== */


</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> طباعة</button>
</div>

<div class="print-area">
  <!-- شعار + عنوان + رقم تسلسلي -->
  <div class="d-flex flex-column align-items-center mb-3">
    <img src="<?= esc(getSystemSettings('secondary_logo') ?: '/assets/logo.png') ?>" class="logo mb-1" alt="Logo" style="width:150px; height:auto;">
    <h2 style="font-weight:bold; color:#000; margin:0;">فاتورة أصل ثابت</h2>
    <div class="invoice-serial">الرقم التسلسلي: <?= esc($asset['invoice_serial']) ?></div>
  </div>

  <div class="invoice-header d-flex align-items-start gap-3">

    <!-- ✅ العمود اليمين: الفرع -->
    <div class="invoice-branch text-end" style="min-width:220px; font-weight:bold;">
      <?php if(!empty($asset['branch_name'])): ?>
        <div>
          <strong>الفرع:</strong><br>
          <?= esc($asset['branch_name']) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ✅ العمود الأوسط: بيانات الفاتورة -->
    <div class="text-end invoice-info" style="flex:1">
      <div><strong>رقم الفاتورة:</strong> <?= esc($asset['bill_number']) ?></div>
      <div><strong>الدافع:</strong> <?= esc($asset['payer_name']) ?></div>
      <div><strong>مصدر الدفع:</strong> <?= esc($asset['payment_source']) ?></div>

      <?php if(has_permission('assets.edit_assets_invoice_date')): ?>
        <div>
          <strong>التاريخ:</strong>
          <input type="date"
                id="invoiceDate"
                value="<?= date('Y-m-d', strtotime($asset['created_at'])) ?>"
                data-asset-id="<?= $assetId ?>"
                style="border:1px solid #ccc; border-radius:4px; padding:2px 6px;">
          <span id="invoiceDateText" style="display:none;font-weight:bold;">
            <?= date('Y-m-d', strtotime($asset['created_at'])) ?>
          </span>
        </div>
      <?php else: ?>
        <div><strong>التاريخ:</strong> <?= date('Y-m-d', strtotime($asset['created_at'])) ?></div>
      <?php endif; ?>
    </div>

    <!-- ✅ العمود الشمال: صورة الفاتورة -->
    <?php if($invoiceImage): ?>
      <div class="invoice-image-wrapper">
        <a href="uploads/<?= esc($invoiceImage) ?>" target="_blank">
          <img src="uploads/<?= esc($invoiceImage) ?>" class="invoice-image" alt="Asset File">
        </a>
      </div>
    <?php endif; ?>

  </div>


  <!-- جدول الأصل -->
  <div class="table-responsive">
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
  </table></div>

  <!-- الملخص -->
  <!--<div class="invoice-summary">
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
</div>-->
<div class="invoice-container" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 30px; direction: rtl;">

  <!-- ✅ العمود اليمين: المبلغ بالعربي -->
  <div class="total-words" style="font-weight: bold; color: #444; font-size: 15px; text-align: right; margin-top: 35px;">
    (<?= numberToArabicWords($asset['total_amount']) ?> فقط)
  </div>

  <!-- ✅ العمود الشمال: تفاصيل الفاتورة -->
  <div class="invoice-summary-wrapper" style="margin-top: 25px;">
    <div class="invoice-summary" style="text-align: right;">
      <?php if(has_permission('assets.edit_assets_invoice_tax')): ?>
      <div>
        <strong>نسبة الضريبة:</strong>
        <select id="vatRate" data-asset-id="<?= $assetId ?>">
          <option value="0" <?= $vatRate == 0 ? 'selected' : '' ?>>0%</option>
          <option value="0.15" <?= $vatRate == 0.15 ? 'selected' : '' ?>>15%</option>
        </select>
        <span id="vatRateText"><?= $vatRate == 0 ? '0%' : '15%' ?></span>
      </div>
      <?php else: ?>
        <div>
          <strong>نسبة الضريبة :</strong>
          <span><?= $vatRate ?></span> %
        </div>
      <?php endif; ?>

      <div>
        <strong>المجموع:</strong>
        <span id="totalNoVat"><?= number_format($subtotal,2) ?></span> ريال
      </div>

      <div id="vatRow" <?= $vatRate == 0 ? 'style="display:none;"' : '' ?>>
        <strong>الضريبة:</strong>
        <span id="vatValue"><?= number_format($asset['vat_value'],2) ?></span> ريال
      </div>

      <div id="grandRow" <?= $vatRate == 0 ? 'style="display:none;"' : '' ?>>
        <strong>الإجمالي بعد الضريبة:</strong>
        <span id="grandTotal"><?= number_format($asset['total_amount'],2) ?></span> ريال
      </div>
    </div>
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
