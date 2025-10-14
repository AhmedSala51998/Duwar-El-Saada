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
  /* لجعل الصور ثابتة الحجم في الجدول */
/* لجعل الصور في الجدول بحجم ثابت وصندوق موحد */
.table td label.custom-file-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 130px;        /* عرض الصندوق */
  height: 100px;       /* ارتفاع الصندوق */
  border: 1px dashed #ccc;
  padding: 5px;
  cursor: pointer;
  overflow: hidden;
  box-sizing: border-box;
  border-radius: 8px;  /* اختياري: حواف مدورة */
  background-color: #f9f9f9;
}

/* الصورة نفسها */
.table td label.custom-file-upload img {
  width: 100%;
  height: 100%;
  object-fit: contain; /* يجعل الصورة تظهر كاملة داخل الصندوق بدون قص */
  border-radius: 4px;
}

/* أيقونة ونص قبل رفع الصورة */
.table td label.custom-file-upload i,
.table td label.custom-file-upload span {
  position: absolute;   /* تظهر فوق الصورة قبل الاختيار */
  pointer-events: none; /* لا تمنع النقر على input */
}

/* إخفاء النص والأيقونة بعد رفع الصورة */
.table td label.custom-file-upload img[src] ~ i,
.table td label.custom-file-upload img[src] ~ span {
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

$q = "SELECT p.*, o.invoice_serial 
      FROM purchases p 
      LEFT JOIN orders_purchases o ON p.order_id = o.id
      WHERE 1";

$params = [];
if($kw !== '') { 
    $q .= " AND p.name LIKE ?"; 
    $params[] = "%$kw%"; 
}

$q .= " ORDER BY p.id DESC";

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
        <a class="btn btn-outline-success" href="uploads/purchases_sample_template.xlsx" download>
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
      <th>#</th><th>رقم تسلسلي</th><th>البيان</th><th>نوع الوحدة</th><th>الكمية</th><th>السعر</th><th>الكميات بالوحدة</th><th>اجمالي الكميات</th><th>السعر الافرادي</th><th>التاريخ</th><th>الدافع</th><th>مصدر الدفع</th>
      <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= esc($r['invoice_serial'] ?? '-') ?></td>
      <!--<td><?php if($r['product_image']): ?><img src="uploads/<?= esc($r['product_image']) ?>" width="44" class="rounded"><?php endif; ?></td>-->
      <td><?= esc($r['name']) ?></td>
      <td><?= esc($r['unit']) ?></td>
      <td>
        <span class="badge badge-unit">
          <?= htmlspecialchars($r['total_packages']) ?>
          <?php if (!empty($r['package'])): ?>
            × <?= htmlspecialchars($r['package']) ?>
          <?php endif; ?>
        </span>
      </td>
      <td><?= number_format((float)$r['total_price'],7) ?></td>
      <td>
        <span class="badge badge-unit">
          <?= htmlspecialchars($r['single_package']) ?>
        </span>
      </td>
      <td>
        <span class="badge badge-unit">
          <?= htmlspecialchars($r['quantity']) ?>
        </span>
      </td>
      <td><?= number_format((float)$r['price'],7) ?></td>
      <td><?= esc($r['created_at']) ?></td>
      <!--<td>
      <?php if($r['invoice_image']): ?>
        <a href="uploads/<?= esc($r['invoice_image']) ?>" target="_blank">
          <img src="uploads/<?= esc($r['invoice_image']) ?>" width="44" class="rounded shadow-sm">
        </a>
      <?php endif; ?>
      </td>-->
      <td><?= esc($r['payer_name']) ?></td>
      <td><?= esc($r['payment_source'] ?? '-') ?></td>
      <?php if($can_edit): ?>
      <td class="table-actions">
        <a class="btn btn-sm btn-outline-primary" href="invoice?id=<?= $r['id'] ?>"><i class="bi bi-printer"></i></a>
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
                    <label class="form-label">نوع الوحدة</label>
                    <select name="unit" class="form-select">
                      <?php foreach(['عدد','كيلو','لتر'] as $u): ?>
                        <option <?= $r['unit']===$u?'selected':'' ?>><?= $u ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">الوحدة \ العبوة</label>
                    <input type="text" name="package" class="form-control" value="<?= esc($r['package'] ?? '') ?>" placeholder="أدخل العبوة">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">السعر</label>
                    <input type="number" step="0.00000001" min="0" name="price" class="form-control" value="<?= esc($r['price']) ?>">
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
<div class="modal fade" id="addM">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="purchase_add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header">
          <h5 class="modal-title">إضافة أصناف متعددة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label>رقم فاتورة المورد</label>
            <input type="number" name="bill_number" id="bill_number" class="form-control" required
                  placeholder="أدخل رقم فاتورة المورد المكون من 15 رقم">
            <div class="invalid-feedback">رقم فاتورة المورد يجب أن يكون 15 رقم بالضبط.</div>
          </div>
          <div class="mb-3">
            <label>الرقم الضريبي للمورد</label>
            <input type="text" name="tax_number" id="tax_number" class="form-control" maxlength="15" pattern="\d{15}" required
                  placeholder="أدخل الرقم الضريبي للمورد المكون من 15 رقم">
            <div class="invalid-feedback">الرقم الضريبي للمورد يجب أن يكون 15 رقم بالضبط.</div>
          </div>
          <div class="mb-3">
            <label>اسم المورد</label>
            <input type="text" name="supplier_name" class="form-control" id="supplier_name" required>
          </div>

          <div class="mb-3">
            <label>تاريخ الفاتورة</label>
            <input type="date" name="invoice_date" class="form-control" id="invoice_date" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label>اسم الدافع</label>
              <select name="payer_name" class="form-select payer-select">
                  <option hidden>اختر</option>
                  <option>شركة</option>
                  <option>مؤسسة</option>
                  <option>فيصل المطيري</option>
                  <option>بسام</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>مصدر الدفع</label>
              <select name="payment_source" class="form-select payment-source-select">
                  <option hidden>اختر</option>
                  <option>مالك</option>
                  <option>كاش</option>
                  <option>بنك</option>
                </select>
            </div>
          </div>

          <table class="table table-bordered" id="itemsTable">
            <thead>
              <tr>
                <th>البيان</th>
                <th>نوع الوحدة</th>
                <th>الوحدة \ العبوة</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الكمية بالوحدة</th>
                <!--<th>اسم الدافع</th>
                <th>مصدر الدفع</th>-->
                <th>إزالة</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input name="name[]" class="form-control" required></td>
                <td>
                  <select title="نوع الوحدة" name="unit[]" class="form-select">
                    <option>عدد</option>
                    <option>كيلو</option>
                    <option>لتر</option>
                  </select>
                </td>
                <td><input name="package[]" class="form-control" title="الوحدة"></td> <!-- حقل العبوة -->
                <td><input type="number" step="0.001" min="0" name="quantity[]" class="form-control" required></td>
                <td><input type="number" step="0.00000001" min="0" name="price[]" class="form-control"></td>
                <td><input type="number" step="0.001" min="0" name="single_package[]" class="form-control"></td>
                <!--<td>
                  <select name="payer_name[]" class="form-select payer-select">
                    <option hidden>اختر</option>
                    <option>شركة</option>
                    <option>مؤسسة</option>
                    <option>فيصل المطيري</option>
                    <option>بسام</option>
                  </select>
                </td>
                <td>
                  <select name="payment_source[]" class="form-select payment-source-select">
                    <option hidden>اختر</option>
                    <option>مالك</option>
                    <option>كاش</option>
                    <option>بنك</option>
                  </select>
                </td>-->
                <td>
                  <button type="button" class="btn btn-danger btn-sm remove-row">✖</button>
                </td>
              </tr>
            </tbody>
          </table>


          <button type="button" id="addRow" class="btn btn-secondary">+ إضافة صف جديد</button>

          <hr>

          <div class="mt-4">
            <label>صورة الفاتورة العامة</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-receipt"></i>
              <span id="file-text-inv-main"></span>
              <input type="file" name="invoice_image" accept="image/*"
                     onchange="previewFile(this,'file-text-inv-main','preview-inv-main')">
              <img id="preview-inv-main" style="display:none; max-width:150px; margin-top:10px"/>
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if($can_edit): ?>
<div class="modal fade" id="importExcel">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="purchase_import_excel" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-file-earmark-spreadsheet"></i> استيراد أصناف من ملف Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label>رقم فاتورة المورد</label>
            <input type="number" name="bill_number" class="form-control" required placeholder="أدخل رقم فاتورة المورد المكون من 15 رقم">
          </div>

          <div class="mb-3">
            <label>الرقم الضريبي للمورد</label>
            <input type="text" name="tax_number" class="form-control" maxlength="15" pattern="\d{15}" required placeholder="أدخل الرقم الضريبي للمورد المكون من 15 رقم">
          </div>

          <div class="mb-3">
            <label>اسم المورد</label>
            <input type="text" name="supplier_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>تاريخ الفاتورة</label>
            <input type="date" name="invoice_date" class="form-control" required>
          </div>

          <div class="row">
              <div class="col-md-6 mb-3">
                <label>اسم الدافع</label>
                <select name="payer_name" class="form-select payer-select">
                    <option hidden>اختر</option>
                    <option>شركة</option>
                    <option>مؤسسة</option>
                    <option>فيصل المطيري</option>
                    <option>بسام</option>
                  </select>
              </div>

              <div class="col-md-6 mb-3">
                <label>مصدر الدفع</label>
                <select name="payment_source" class="form-select payment-source-select">
                    <option hidden>اختر</option>
                    <option>مالك</option>
                    <option>كاش</option>
                    <option>بنك</option>
                  </select>
              </div>
            </div>

          <div class="mb-3">
            <label>اختر ملف Excel</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-cloud-arrow-up"></i>
              <span id="file-text-excel">اختر ملف Excel</span>
              <input required type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls"
                     onchange="document.getElementById('file-text-excel').textContent=this.files[0].name">
            </label>
          </div>

          <div class="alert alert-info mt-3">
            📘 يجب أن يحتوي ملف الإكسل على الأعمدة التالية (بنفس الأسماء):  
            <ul class="mb-0">
              <li><b>name</b> : البيان</li>
              <li><b>quantity</b> : الكمية</li>
              <li><b>unit_type</b> : نوع الوحدة</li>
              <li><b>unit</b> : الوحدة \ العبوة</li>
              <li><b>price</b> : السعر</li>
              <li><b>unit_quantity</b> : الكميات بالوحدة</li>
              <!--<li><b>payer_name</b> : اسم الدافع</li>
              <li><b>payment_source</b> : مصدر الدفع</li>-->
            </ul>
          </div>

          <hr>

          <div class="mt-4">
            <label>صورة الفاتورة العامة (اختياري)</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-receipt"></i>
              <span id="file-text-inv-main"></span>
              <input type="file" name="invoice_image" accept="image/*"
                     onchange="previewFile(this,'file-text-inv-main','preview-inv-main')">
              <img id="preview-inv-main" style="display:none; max-width:150px; margin-top:10px"/>
            </label>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-orange"><i class="bi bi-check2-circle"></i> استيراد الأصناف</button>
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
<script>
// استمع لظهور المودال
document.addEventListener('shown.bs.modal', function (event) {
  const modal = event.target;

  modal.addEventListener('change', function (e) {
    if (e.target && e.target.classList.contains('payer-select')) {
      const payerSelect = e.target;

      // بدل closest('tr') استخدم أقرب div.row
      const container = payerSelect.closest('.row');
      if (!container) return;

      const paymentSelect = container.querySelector('.payment-source-select');
      if (!paymentSelect) return;

      const payer = payerSelect.value;
      if (!payer) return;

      fetch('get_custody_amount?person_name=' + encodeURIComponent(payer))
        .then(res => res.json())
        .then(data => {
          const existing = paymentSelect.querySelector('option[data-custody]');
          if (existing) existing.remove();

          if (data.amount && data.amount > 0) {
            const option = document.createElement('option');
            option.value = 'عهدة';
            option.textContent = 'عهدة (الرصيد: ' + parseFloat(data.amount).toFixed(2) + ')';
            option.setAttribute('data-custody', '1');
            paymentSelect.appendChild(option);
          }
        })
        .catch(err => console.error(err));
    }
  });
});


</script>
<script>
// preview image
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

// add new row
let rowIndex = 1;
document.getElementById('addRow').addEventListener('click', function() {
  const tbody = document.querySelector('#itemsTable tbody');
  const newRow = document.createElement('tr');

  newRow.innerHTML = `
    <td><input name="name[]" class="form-control" required></td>
    <td>
      <select title="نوع الوحدة" name="unit[]" class="form-select">
        <option>عدد</option>
        <option>كيلو</option><option>لتر</option>
      </select>
    </td>
    <td><input name="package[]" class="form-control" title="الوحدة"></td>
    <td><input type="number" step="0.001" min="0" name="quantity[]" class="form-control" required></td>
    <td><input type="number" step="0.00000001" min="0" name="price[]" class="form-control"></td>
    <td><input type="number" step="0.001" min="0" name="single_package[]" class="form-control"></td>
    <!--<td>
      <select name="payer_name[]" class="form-select payer-select">
        <option hidden>اختر</option>
        <option>شركة</option><option>مؤسسة</option>
        <option>فيصل المطيري</option><option>بسام</option>
      </select>
    </td>
    <td>
      <select name="payment_source[]" class="form-select payment-source-select">
        <option hidden>اختر</option>
        <option>مالك</option><option>كاش</option><option>بنك</option>
      </select>
    </td>-->
    <td><button type="button" class="btn btn-danger btn-sm remove-row">✖</button></td>
  `;

  tbody.appendChild(newRow);
  rowIndex++;
});

// remove row
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('remove-row')) {
    e.target.closest('tr').remove();
  }
});
</script>
<script>
document.querySelector('form[action="purchase_add"]').addEventListener('submit', function(e) {
  e.preventDefault(); // نوقف الإرسال مؤقتاً

  const taxInput = document.getElementById('tax_number');
  const taxValue = taxInput.value.trim();

  const billNumber = document.getElementById('bill_number').value.trim();
  const supplierName = document.getElementById('supplier_name').value.trim();

  // تحقق من الرقم الضريبي
  if (!/^\d{15}$/.test(taxValue)) {
    alert('الرقم الضريبي يجب أن يكون 15 رقم بالضبط.');
    taxInput.focus();
    return;
  }

  if (!billNumber) {
    alert('يرجى إدخال رقم فاتورة المورد.');
    return;
  }

  if (!supplierName) {
    alert('يرجى إدخال اسم المورد.');
    return;
  }

  // نتحقق أولاً من رقم الفاتورة
  fetch('check_bill_number?bill=' + encodeURIComponent(billNumber) + '&supplier=' + encodeURIComponent(supplierName))
    .then(res => res.json())
    .then(data => {
      if (data.exists) {
        alert('⚠️ فاتورة المورد هذه موجودة من قبل!');
      } else {
        // بعد التأكد من رقم الفاتورة، نتحقق من الرقم الضريبي
        fetch('check_tax_number?tax=' + encodeURIComponent(taxValue))
          .then(res => res.json())
          .then(data2 => {
            if (data2.exists) {
              alert('هذا الرقم الضريبي مستخدم بالفعل!');
            } else {
              e.target.submit(); // أرسل النموذج فعلياً بعد نجاح كل الفحوص
            }
          })
          .catch(() => {
            alert('حدث خطأ أثناء التحقق من الرقم الضريبي.');
          });
      }
    })
    .catch(() => {
      alert('حدث خطأ أثناء التحقق من رقم الفاتورة.');
    });
});
</script>
