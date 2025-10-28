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

/* Pagination Styling */
.pagination .page-link {
    color: #ff6a00;
    border-color: #ff6a00;
    transition: all 0.2s ease-in-out;
}

.pagination .page-item.active .page-link {
    background-color: #ff6a00;
    border-color: #ff6a00;
    color: #fff;
}

.pagination .page-link:hover {
    background-color: #ff6a00;
    color: #fff;
    border-color: #ff6a00;
}

.pagination .page-item.disabled .page-link {
    color: #aaa;
    border-color: #ccc;
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

$perPage = 10; // عدد النتائج في الصفحة
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// حساب إجمالي الصفوف
$count_q = "SELECT COUNT(*) AS total FROM assets WHERE 1";
$count_params = [];
if($kw!==''){
  $count_q .= " AND name LIKE ?";
  $count_params[] = "%$kw%";
}

$stmtCount = $pdo->prepare($count_q);
$stmtCount->execute($count_params);
$total_rows = $stmtCount->fetch()['total'];
$total_pages = ceil($total_rows / $perPage);
$offset = ($page - 1) * $perPage;

// جلب الصفوف الفعلية
$q = "SELECT * FROM assets WHERE 1";
$ps = [];
if($kw!==''){ 
  $q.=" AND name LIKE ?"; 
  $ps[]="%$kw%"; 
} 
$q.=" ORDER BY id DESC LIMIT $perPage OFFSET $offset";

$s=$pdo->prepare($q);
$s->execute($ps);
$rows=$s->fetchAll();
$can_edit = in_array(current_role(), ['admin','manager']);
?>

<!--<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
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
</div>-->

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
  <h3 class="mb-0">الأصول</h3>

  <div class="d-flex flex-wrap align-items-center gap-2">
    <form class="d-flex align-items-center gap-2 mb-0" method="get" style="height:40px;">
      <input class="form-control" name="kw" placeholder="بحث بالاسم" value="<?= esc($kw) ?>" style="height:40px; min-width:200px;">
      <button class="btn btn-outline-secondary" style="height:40px;">بحث</button>
    </form>

    <a class="btn btn-outline-dark" href="export_assets_excel.php?kw=<?= urlencode($kw) ?>" style="height:40px;">
      <i class="bi bi-file-earmark-spreadsheet"></i> Excel
    </a>

    <a class="btn btn-outline-dark" href="export_assets_pdf.php?kw=<?= urlencode($kw) ?>" style="height:40px;">
      <i class="bi bi-filetype-pdf"></i> PDF
    </a>

    <?php if($can_edit): ?>
      <button class="btn btn-orange d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add" style="height:40px;">
        <i class="bi bi-plus-lg me-1"></i> إضافة
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th><th>الرقم التسلسلي</th><th>صورة</th><th>الاسم</th><th>النوع</th><th>العدد</th><th>السعر</th><th>الضريبة (15%)</th>
      <th>الإجمالي بعد الضريبة</th>
      <th>الدافع</th><th>مصدر الدفع</th>
      <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= $r['invoice_serial'] ?></td>
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
      <!--<td class="table-actions">
        <a class="btn btn-sm btn-outline-primary" href="invoice_assest?id=<?= $r['id'] ?>"><i class="bi bi-printer"></i></a>
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
      </td>-->
      <td class="text-center">
          <!-- زر الترس -->
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionsAsset<?= $r['id'] ?>">
            <i class="bi bi-gear-fill"></i>
          </button>

          <!-- مودال العمليات -->
          <div class="modal fade" id="actionsAsset<?= $r['id'] ?>" tabindex="-1" aria-labelledby="actionsAssetLabel<?= $r['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="actionsAssetLabel<?= $r['id'] ?>">
                    <i class="bi bi-gear-fill me-1"></i> العمليات
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">

                  <!-- زر الطباعة -->
                  <a class="btn btn-outline-primary w-100 mb-2" href="invoice_assest?id=<?= $r['id'] ?>">
                    <i class="bi bi-printer me-2"></i> طباعة
                  </a>

                  <!-- زر التعديل -->
                  <button class="btn btn-outline-warning w-100 mb-2"
                          data-bs-dismiss="modal"
                          data-bs-toggle="modal"
                          data-bs-target="#e<?= $r['id'] ?>">
                    <i class="bi bi-pencil me-2"></i> تعديل
                  </button>

                  <!-- زر الحذف -->
                  <button class="btn btn-outline-danger w-100"
                          data-bs-dismiss="modal"
                          data-bs-toggle="modal"
                          data-bs-target="#del<?= $r['id'] ?>">
                    <i class="bi bi-trash me-2"></i> حذف
                  </button>

                </div>
              </div>
            </div>
          </div>
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
              <input type="number" name="quantity" id="quantity_edit_<?= $r['id'] ?>" class="form-control" value="<?= (int)$r['quantity'] ?>" min="0">

              <label>السعر</label>
              <input type="number" step="0.01" min="0" name="price" id="price_edit_<?= $r['id'] ?>" class="form-control" 
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
                // جلب مجموع العهدة للشخص
                $stmtC = $pdo->prepare("SELECT SUM(amount) AS total_amount FROM custodies WHERE person_name=?");
                $stmtC->execute([$r['payer_name']]);
                $custody = $stmtC->fetch();

                $totalCustody = $custody['total_amount'] ?? 0;

                if($totalCustody > 0){
                    echo '<option value="عهدة" '.($r['payment_source']=='عهدة'?'selected':'').'>عهدة ('.$totalCustody.' ريال)</option>';
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
              <button name="save" type="submit" class="btn btn-orange">حفظ</button>
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
<?php if ($total_pages > 1): ?>
<nav aria-label="صفحات النتائج" class="mt-3">
  <ul class="pagination justify-content-center flex-wrap">
    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=1">الأول</a>
    </li>

    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $page - 1 ?>">السابق</a>
    </li>

    <?php
    $max_links = 5;
    $start = max($page - 2, 1);
    $end = min($page + 2, $total_pages);

    if($start > 1){
        echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }

    for($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor;

    if($end < $total_pages){
        echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    ?>

    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $page + 1 ?>">التالي</a>
    </li>

    <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $total_pages ?>">الأخير</a>
    </li>
  </ul>
</nav>
<?php endif; ?>


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
          <div><label class="form-label">رقم فاتورة المورد</label>
          <input type="number" name="bill_number" required class="form-control"></div>
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
            <input type="number" step="0.01" min="0" name="price" class="form-control">
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
        <div class="modal-footer"><button name="save" type="submit" class="btn btn-orange">حفظ</button></div>
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

