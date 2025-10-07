<!-- CSS للستايل -->
<style>
.custom-file-upload {
    border: 2px dashed #ccc;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease-in-out;
    background: #f9f9f9;
}
.custom-file-upload:hover {
    border-color: #0d6efd;
    background: #eef5ff;
}
.custom-file-upload i {
    font-size: 40px;
    color: #0d6efd;
    margin-bottom: 10px;
}
.custom-file-upload span {
    font-size: 14px;
    color: #666;
}
.custom-file-upload img {
    max-height: 120px;
    margin-top: 10px;
    border-radius: 8px;
}
input[type="file"] {
    display: none;
}
</style>

<?php require __DIR__.'/partials/header.php'; ?>

<?php if(!empty($_SESSION['toast'])): 
$toast = $_SESSION['toast'];
unset($_SESSION['toast']); 
?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 2000">
  <div id="liveToast" class="toast align-items-center text-bg-<?= $toast['type'] ?> border-0 show fade" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body"><?= esc($toast['msg']) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("liveToast");
    if(el){
      let toast = new bootstrap.Toast(el, { delay: 2500 });
      toast.show();
    }
});
</script>
<?php endif; ?>

<?php
$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM assets WHERE 1"; 
$ps=[]; 
if($kw!==''){ 
  $q.=" AND name LIKE ?"; 
  $ps[]="%$kw%"; 
} 
$q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); 
$s->execute($ps); 
$rows=$s->fetchAll();
$can_edit = in_array(current_role(), ['admin','manager']);
?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h3 class="mb-0">الأصول</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث بالاسم" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <a class="btn btn-outline-dark" href="export_assets_excel.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_assets_pdf.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <?php if($can_edit): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> إضافة</button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th><th>صورة</th><th>الاسم</th><th>النوع</th><th>العدد</th><th>السعر</th><th>الضريبة (15%)</th>
      <th>الإجمالي بعد الضريبة</th>
      <th>الدافع</th><th>مصدر الدفع</th>
      <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?php if($r['image']): ?><img src="uploads/<?= esc($r['image']) ?>" width="44" class="rounded"><?php endif; ?></td>
      <td><?= esc($r['name']) ?></td>
      <td><?= esc($r['type']) ?></td>
      <td><?= (int)$r['quantity'] ?></td>
      <td><?= number_format((float)$r['price'],2) ?></td>
      <td>
          <?php if (!empty($r['has_vat']) && $r['has_vat'] == 1): ?>
            <?= number_format((float)$r['vat_value'],2) ?>
          <?php else: ?>
            <span class="text-muted small">بدون</span>
          <?php endif; ?>
        </td>

        <td>
          <?= number_format((float)$r['total_amount'],2) ?>
        </td>

      <td><?= esc($r['payer_name']) ?></td>
      <td><?= esc($r['payment_source'] ?? '-') ?></td>
      <?php if($can_edit): ?>
      <td class="table-actions">
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
      </td>
      <?php endif; ?>
    </tr>

    <!-- Modal تعديل -->
    <?php if($can_edit): ?>
    <div class="modal fade" id="e<?= $r['id'] ?>">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" action="asset_edit" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">

            <div class="modal-header">
              <h5 class="modal-title">تعديل أصل</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body vstack gap-3">
              <label>الاسم</label>
              <input name="name" class="form-control" value="<?= esc($r['name']) ?>" required>

              <label>النوع</label>
              <input name="type" class="form-control" value="<?= esc($r['type']) ?>">

              <label>العدد</label>
              <input type="number" name="quantity" class="form-control" value="<?= (int)$r['quantity'] ?>" min="1">

              <label>السعر</label>
              <input type="number" step="0.01" name="price" id="price_edit_<?= $r['id'] ?>" class="form-control" 
                    value="<?= esc($r['price']) ?>" oninput="updateAssetVat('<?= $r['id'] ?>')">

              <label>هل الأصل عليه ضريبة؟</label>
              <select name="has_vat" id="has_vat_edit_<?= $r['id'] ?>" class="form-select" onchange="updateAssetVat('<?= $r['id'] ?>')">
                <option value="0" <?= ($r['has_vat']==0)?'selected':'' ?>>لا</option>
                <option value="1" <?= ($r['has_vat']==1)?'selected':'' ?>>نعم</option>
              </select>

              <div id="vat_section_edit_<?= $r['id'] ?>" style="<?= $r['has_vat'] ? '' : 'display:none;' ?>">
                <label>نسبة الضريبة (٪)</label>
                <input type="number" step="0.01" name="vat_percent" id="vat_percent_edit_<?= $r['id'] ?>" value="15" class="form-control" readonly>

                <label>إجمالي بعد الضريبة</label>
                <input type="text" id="total_with_vat_edit_<?= $r['id'] ?>" class="form-control" readonly
                      value="<?= $r['has_vat'] ? number_format($r['total_amount'],2) : number_format($r['price'],2) ?>">
              </div>

              <label>اسم الدافع</label>
              <select name="payer_name" class="form-control payer-select" data-id="<?= $r['id'] ?>">
                <option hidden>اختر الدافع</option>
                <?php foreach(['شركة','مؤسسة','فيصل المطيري','بسام'] as $payer): ?>
                  <option <?= $r['payer_name']===$payer?'selected':'' ?>><?= $payer ?></option>
                <?php endforeach; ?>
              </select>

              <label>مصدر الدفع</label>
              <select name="payment_source" class="form-control payment-source-select" id="payment_source_<?= $r['id'] ?>">
                <option hidden>اختر مصدر الدفع</option>
                <option value="مالك" <?= $r['payment_source']=='مالك'?'selected':'' ?>>مالك</option>
                <option value="كاش" <?= $r['payment_source']=='كاش'?'selected':'' ?>>كاش</option>
                <option value="بنك" <?= $r['payment_source']=='بنك'?'selected':'' ?>>بنك</option>
                <?php
                  $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
                  $stmtC->execute([$r['payer_name']]);
                  $custody = $stmtC->fetch();
                  if($custody && $custody['amount']>0){
                    echo '<option value="عهدة" '.($r['payment_source']=='عهدة'?'selected':'').'>عهدة ('.$custody['amount'].' ريال)</option>';
                  }
                ?>
              </select>

              <label>صورة</label>
              <label class="custom-file-upload w-100">
                <i class="bi bi-image"></i>
                <span id="file-text-edit-<?= $r['id'] ?>">اختر صورة</span>
                <input type="file" name="image" id="asset_image_edit_<?= $r['id'] ?>" accept="image/*"
                      onchange="previewFile(this,'file-text-edit-<?= $r['id'] ?>','preview-edit-<?= $r['id'] ?>')">
                <?php if(!empty($r['image'])): ?>
                  <img id="preview-edit-<?= $r['id'] ?>" src="<?= 'uploads/'.esc($r['image']) ?>" style="max-width:100px;margin-top:8px;"/>
                <?php else: ?>
                  <img id="preview-edit-<?= $r['id'] ?>" style="display:none;max-width:100px;margin-top:8px;"/>
                <?php endif; ?>
              </label>
            </div>

            <div class="modal-footer">
              <button class="btn btn-orange">حفظ</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Modal الحذف -->
    <?php if($can_edit): ?>
    <div class="modal fade" id="del<?= $r['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">تأكيد الحذف</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            هل أنت متأكد أنك تريد حذف الأصل <b><?= esc($r['name']) ?></b> ؟
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <a href="asset_delete?id=<?= $r['id'] ?>" class="btn btn-danger">حذف</a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if($can_edit): ?>
<div class="modal fade" id="add">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="asset_add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">إضافة أصل</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body vstack gap-3">
          <div><label class="form-label">الاسم</label>
            <input name="name" class="form-control" required>
          </div>
          <div><label class="form-label">النوع</label>
            <input name="type" class="form-control">
          </div>
          <div><label class="form-label">العدد</label>
            <input type="number" name="quantity" class="form-control" value="1" min="1">
          </div>
          <div><label class="form-label">السعر</label>
            <input type="number" step="0.01" name="price" class="form-control">
          </div>
          <label>هل الأصل عليه ضريبة؟</label>
          <select id="asset_has_vat" name="has_vat" class="form-select">
            <option value="0" selected>لا</option>
            <option value="1">نعم</option>
          </select>

          <div id="asset_vat_section" style="display:none;">
            <label>نسبة الضريبة (٪)</label>
            <input type="number" step="0.01" id="asset_vat_percent" name="vat_percent" value="15" class="form-control" readonly>

            <label>إجمالي بعد الضريبة</label>
            <input type="text" id="asset_total_with_vat" class="form-control" readonly>
          </div>
          <div><label class="form-label">اسم الدافع</label>
            <select name="payer_name" class="form-control payer-select">
              <option hidden>اختر الدافع</option>
              <option>شركة</option>
              <option>مؤسسة</option>
              <option>فيصل المطيري</option>
              <option>بسام</option>
            </select>
          </div>
          <div><label class="form-label">مصدر الدفع</label>
            <select name="payment_source" class="form-control payment-source-select">
              <option hidden>اختر مصدر الدفع</option>
              <option>مالك</option>
              <option>كاش</option>
              <option>بنك</option>
            </select>
          </div>

          <div><label class="form-label">صورة</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-image"></i>
              <span id="file-text-asset">اختر صورة</span>
              <input type="file" name="image" id="asset_image" accept="image/*"
                     onchange="previewFile(this,'file-text-asset','preview-asset')">
              <img id="preview-asset" style="display:none;max-width:100px;margin-top:8px;"/>
            </label>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
function previewFile(input, textId, previewId) {
    const file = input.files[0];
    if(file){
        document.getElementById(textId).textContent = file.name;
        const reader = new FileReader();
        reader.onload = function(e){
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
}

// تحديث مصدر الدفع عند اختيار الدافع
document.addEventListener('shown.bs.modal', function(event){
    const modal = event.target;
    const payerSelect = modal.querySelector('.payer-select');
    const paymentSelect = modal.querySelector('.payment-source-select');
    if(!payerSelect || !paymentSelect) return;

    payerSelect.addEventListener('change', function(){
        const payer = this.value;
        if(!payer) return;

        fetch('get_custody_amount.php?person_name=' + encodeURIComponent(payer))
        .then(res => res.json())
        .then(data => {
            const existing = paymentSelect.querySelector('option[data-custody]');
            if(existing) existing.remove();

            if(data.amount && data.amount > 0){
                const option = document.createElement('option');
                option.value = 'عهدة';
                option.textContent = 'عهدة (الرصيد: ' + parseFloat(data.amount).toFixed(2) + ')';
                option.setAttribute('data-custody','1');
                paymentSelect.appendChild(option);
            }
        })
        .catch(err => console.error(err));
    });
});
</script>
<script>
function setupAssetVAT(modal) {
  const hasVat = modal.querySelector('#asset_has_vat');
  const price = modal.querySelector('input[name="price"]');
  const quantity = modal.querySelector('input[name="quantity"]');
  const vatSection = modal.querySelector('#asset_vat_section');
  const vatPercent = modal.querySelector('#asset_vat_percent');
  const totalWithVat = modal.querySelector('#asset_total_with_vat');

  function updateTotal() {
    const amt = parseFloat(price.value) || 0;
    const qty = parseFloat(quantity.value) || 1;
    const vatRate = parseFloat(vatPercent.value) || 0;
    const base = amt * qty;
    if (hasVat.value === '1') {
      const total = base + (base * vatRate / 100);
      totalWithVat.value = total.toFixed(2);
    } else {
      totalWithVat.value = base.toFixed(2);
    }
  }

  hasVat.addEventListener('change', () => {
    vatSection.style.display = hasVat.value === '1' ? 'block' : 'none';
    updateTotal();
  });

  price.addEventListener('input', updateTotal);
  quantity.addEventListener('input', updateTotal);

  // حدث أولي لتحديث المجموع عند فتح المودال
  updateTotal();
}

// استمع لظهور المودال
document.addEventListener('shown.bs.modal', function(event) {
  if(event.target.id === 'add'){ // تأكد ان ده مودال الأصول
    setupAssetVAT(event.target);
  }
});
</script>
<script>
function updateAssetVat(id){
  const price = parseFloat(document.getElementById('price_edit_'+id).value) || 0;
  const quantity = parseFloat(document.getElementById('quantity_edit_'+id).value) || 0; // الكمية
  const totalPrice = price * quantity; // السعر × الكمية

  const hasVat = document.getElementById('has_vat_edit_'+id).value == '1';
  const vatSection = document.getElementById('vat_section_edit_'+id);
  const totalField = document.getElementById('total_with_vat_edit_'+id);
  const vatPercent = 15;

  vatSection.style.display = hasVat ? 'block' : 'none';
  totalField.value = hasVat ? (totalPrice + totalPrice * vatPercent / 100).toFixed(2) : totalPrice.toFixed(2);
}
</script>

