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
      <div class="toast-body">
        <?= esc($toast['msg']) ?>
      </div>
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
$q = "SELECT * FROM purchases WHERE 1";
$params = [];
if($kw!==''){ $q .= " AND name LIKE ?"; $params[] = "%$kw%"; }
$q .= " ORDER BY id DESC";
$stmt = $pdo->prepare($q); 
$stmt->execute($params); 
$rows = $stmt->fetchAll();
$can_edit = in_array(current_role(), ['admin','manager']);
?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h3 class="mb-0">تهيئة المشتريات</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث بالاسم" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <a class="btn btn-outline-dark" href="export_purchases_excel.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_purchases_pdf.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <?php if($can_edit): ?><button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addM"><i class="bi bi-plus-lg"></i> إضافة</button><?php endif; ?>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-success" href="uploads/purchase_template.xlsx" download>
            <i class="bi bi-download"></i> تحميل نموذج Excel
        </a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importExcel">
            <i class="bi bi-file-text"></i> إضافة أصناف عبر Excel
        </button>
    </div>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th><th>صورة</th><th>الاسم</th><th>الكمية</th><th>الوحدة</th><th>السعر</th><th>التاريخ</th><th>فاتورة</th><th>الدافع</th><th>مصدر الدفع</th>
      <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?php if($r['product_image']): ?><img src="uploads/<?= esc($r['product_image']) ?>" width="44" class="rounded"><?php endif; ?></td>
      <td><?= esc($r['name']) ?></td>
      <td><span class="badge badge-unit"><?= $r['quantity'] ?></span></td>
      <td><?= esc($r['unit']) ?></td>
      <td><?= number_format((float)$r['price'],2) ?></td>
      <td><?= esc($r['created_at']) ?></td>
      <td>
      <?php if($r['invoice_image']): ?>
        <a href="uploads/<?= esc($r['invoice_image']) ?>" target="_blank">
          <img src="uploads/<?= esc($r['invoice_image']) ?>" width="44" class="rounded shadow-sm">
        </a>
      <?php endif; ?>
      </td>
      <td><?= esc($r['payer_name']) ?></td>
      <td><?= esc($r['payment_source'] ?? '-') ?></td>
      <?php if($can_edit): ?>
      <td class="table-actions">
        <a class="btn btn-sm btn-outline-primary" href="invoice.php?id=<?= $r['id'] ?>"><i class="bi bi-printer"></i></a>
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>">
          <i class="bi bi-trash"></i>
        </button>
      </td>
      <?php endif; ?>
    </tr>

    <!-- Modal تعديل -->
    <?php if($can_edit): ?>
      <div class="modal fade" id="e<?= $r['id'] ?>">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <form method="post" action="purchase_edit" enctype="multipart/form-data">
              <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <input type="hidden" name="old_price" value="<?= esc($r['price']) ?>">
              <div class="modal-header">
                <h5 class="modal-title">تعديل: <?= esc($r['name']) ?></h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">الاسم</label>
                    <input name="name" class="form-control" value="<?= esc($r['name']) ?>" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">الكمية</label>
                    <input type="number" step="0.001" name="quantity" class="form-control" value="<?= esc($r['quantity']) ?>" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">الوحدة</label>
                    <select name="unit" class="form-select">
                      <?php foreach(['عدد','جرام','كيلو','لتر'] as $u): ?>
                        <option <?= $r['unit']===$u?'selected':'' ?>><?= $u ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">السعر</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= esc($r['price']) ?>">
                  </div>

                  <!-- صورة المنتج -->
                  <div class="col-md-6">
                    <label class="form-label">صورة المنتج</label>
                    <label class="custom-file-upload w-100">
                      <i class="bi bi-cloud-arrow-up"></i>
                      <span id="file-text-prod-<?= $r['id'] ?>">اختر صورة للمنتج</span>
                      <input type="file" name="product_image" id="purchase_product_image_<?= $r['id'] ?>" accept="image/*"
                          onchange="previewFile(this,'file-text-prod-<?= $r['id'] ?>','preview-prod-<?= $r['id'] ?>')">
                      <img id="preview-prod-<?= $r['id'] ?>" src="<?= $r['product_image'] ? 'uploads/'.$r['product_image'] : '' ?>" 
                          style="<?= $r['product_image'] ? 'display:block;max-width:100px;margin-top:8px;' : 'display:none;' ?>"/>
                    </label>
                  </div>

                  <!-- صورة الفاتورة -->
                  <div class="col-md-6">
                    <label class="form-label">صورة الفاتورة</label>
                    <label class="custom-file-upload w-100">
                      <i class="bi bi-receipt"></i>
                      <span id="file-text-inv-<?= $r['id'] ?>">اختر صورة للفاتورة</span>
                      <input type="file" name="invoice_image" id="purchase_invoice_image_<?= $r['id'] ?>" accept="image/*"
                          onchange="previewFile(this,'file-text-inv-<?= $r['id'] ?>','preview-inv-<?= $r['id'] ?>')">
                      <img id="preview-inv-<?= $r['id'] ?>" src="<?= $r['invoice_image'] ? 'uploads/'.$r['invoice_image'] : '' ?>" 
                          style="<?= $r['invoice_image'] ? 'display:block;max-width:100px;margin-top:8px;' : 'display:none;' ?>"/>
                    </label>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">اسم الدافع</label>
                    <select name="payer_name" class="form-select payer-select" data-id="<?= $r['id'] ?>">
                      <option hidden>اختر الدافع</option>
                      <?php foreach (['شركة','مؤسسة','فيصل المطيري','بسام'] as $payer): ?>
                        <option <?= $r['payer_name']===$payer?'selected':'' ?>><?= $payer ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">مصدر الدفع</label>
                    <select name="payment_source" class="form-select" id="payment_source_<?= $r['id'] ?>">
                      <option hidden>اختر مصدر الدفع</option>
                      <option value="مالك" <?= $r['payment_source']=='مالك'?'selected':'' ?>>مالك</option>
                      <option value="كاش" <?= $r['payment_source']=='كاش'?'selected':'' ?>>كاش</option>
                      <option value="بنك" <?= $r['payment_source']=='بنك'?'selected':'' ?>>بنك</option>
                      <?php
                        // جلب رصيد العهدة إذا موجودة
                        $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
                        $stmtC->execute([$r['payer_name']]);
                        $custody = $stmtC->fetch();
                        if($custody && $custody['amount']>0){
                          echo '<option value="عهدة" '.($r['payment_source']=='عهدة'?'selected':'').'>عهدة ('.$custody['amount'].' ريال)</option>';
                        }
                      ?>
                    </select>
                  </div>

                </div>
              </div>
              <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
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
            هل أنت متأكد أنك تريد حذف <b><?= esc($r['name']) ?></b> ؟
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <a href="purchase_delete?id=<?= $r['id'] ?>" class="btn btn-danger">حذف</a>
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
<div class="modal fade" id="addM"><div class="modal-dialog modal-lg"><div class="modal-content">
  <form method="post" action="purchase_add" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
    <div class="modal-header"><h5 class="modal-title">إضافة صنف</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">الاسم</label><input name="name" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">الكمية</label><input type="number" step="0.001" name="quantity" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">الوحدة</label><select name="unit" class="form-select"><option>عدد</option><option>جرام</option><option>كيلو</option><option>لتر</option></select></div>
        <div class="col-md-4"><label class="form-label">السعر</label><input type="number" step="0.01" name="price" class="form-control"></div>

        <!-- صورة المنتج -->
        <div class="col-md-6">
          <label class="form-label">صورة المنتج</label>
          <label class="custom-file-upload w-100">
            <i class="bi bi-cloud-arrow-up"></i>
            <span id="file-text-prod">اختر صورة للمنتج</span>
            <input type="file" name="product_image" id="purchase_product_image" accept="image/*"
                  onchange="previewFile(this,'file-text-prod','preview-prod')">
            <img id="preview-prod" style="display:none"/>
          </label>
        </div>

        <!-- صورة الفاتورة -->
        <div class="col-md-6">
          <label class="form-label">صورة الفاتورة</label>
          <label class="custom-file-upload w-100">
            <i class="bi bi-receipt"></i>
            <span id="file-text-inv">اختر صورة للفاتورة</span>
            <input type="file" name="invoice_image" id="purchase_invoice_image" accept="image/*"
                  onchange="previewFile(this,'file-text-inv','preview-inv')">
            <img id="preview-inv" style="display:none"/>
          </label>
        </div>

        <div class="col-md-6"><label class="form-label">اسم الدافع</label>
          <select name="payer_name" class="form-control payer-select">
            <option hidden>اختر الدافع</option>
            <option>شركة</option>
            <option>مؤسسة</option>
            <option>فيصل المطيري</option>
            <option>بسام</option>
          </select>
        </div>

        <div class="col-md-6"><label class="form-label">مصدر الدفع</label>
          <select name="payment_source" class="form-control payment-source-select">
            <option hidden>اختر مصدر الدفع</option>
            <option>مالك</option>
            <option>كاش</option>
            <option>بنك</option>
          </select>
        </div>

      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
  </form>
</div></div></div>
<?php endif; ?>

<?php if($can_edit): ?>
<div class="modal fade" id="importExcel">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="purchase_import_excel" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-file-earmark-spreadsheet"></i> استيراد أصناف من Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">اختر ملف Excel</label>
          <label class="custom-file-upload w-100">
            <i class="bi bi-cloud-arrow-up"></i>
            <span id="file-text-excel">اختر ملف Excel</span>
            <input required type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls"
                   onchange="document.getElementById('file-text-excel').textContent=this.files[0].name">
          </label>
          <div class="alert alert-info mt-3">
            📌 يجب أن يحتوي ملف الإكسل على الأعمدة بالـ **keys** التالية:  
            <ul class="mb-0">
              <li><b>name</b> : اسم المنتج</li>
              <li><b>quantity</b> : الكمية</li>
              <li><b>unit</b> : الوحدة</li>
              <li><b>price</b> : السعر</li>
              <li><b>payer_name</b> : اسم الدافع</li>
              <li><b>image_path</b> : صورة المنتج</li>
              <li><b>invoice_path</b> : صورة الفاتورة</li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> رفع</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
function previewFile(input, textId, previewId) {
  const file = input.files[0];
  if (file) {
    document.getElementById(textId).textContent = file.name;
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById(previewId);
      preview.src = e.target.result;
      preview.style.display = "block";
    };
    reader.readAsDataURL(file);
  }
}

document.querySelector('#importExcel form').addEventListener('submit', function(e){
  const fileInput = document.getElementById('excel_file');
  if(!fileInput.files.length){
      e.preventDefault();
      alert('❌ الرجاء اختيار ملف Excel أولاً');
  }
});
</script>
