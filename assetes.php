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

/* --- تحسين مظهر الجدول --- */
.custom-table {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem; /* تصغير النص قليلاً للراحة البصرية */
}

.custom-table thead th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
  vertical-align: middle;
  font-size: 0.85rem; /* تصغير الخط في العناوين */
  white-space: nowrap; /* منع كسر السطر في العناوين */
}

.custom-table tbody tr {
  transition: all 0.2s ease-in-out;
}

.custom-table tbody tr:hover {
  background-color: #f1f5ff;
  box-shadow: inset 0 0 0 9999px rgba(0,0,0,0.02);
}


.custom-table td,
.custom-table th {
  padding: 0.6rem 0.75rem;
  vertical-align: middle;
}

.custom-table .badge {
  font-size: 0.8rem;
  border-radius: 0.5rem;
  background: #f0f2f5;
}

.custom-table td {
  white-space: normal !important; /* السماح بالنزول للسطر */
  word-break: break-word; /* كسر الكلمات الطويلة */
  vertical-align: top; /* خليه يبدأ من فوق */
  line-height: 1.4;
}

.small-header th {
  padding: 0.5rem 0.6rem;
}

/* جعل الجدول أنحف وأنيق */
.table-responsive {
  border-radius: 0.75rem;
}

.custom-table th:first-child {
    width: 60px; /* عرض ثابت */
    font-size: 0.75rem; /* تصغير الخط */
    text-align: center;
}
.custom-table td:first-child {
    text-align: center;
    font-size: 0.75rem;
}

.custom-table th:nth-child(9),
.custom-table td:nth-child(9) {
    width: 60px; /* عرض ثابت */
    font-size: 0.75rem; /* تصغير الخط */
    text-align: center;
}

.custom-table th:nth-child(9),
.custom-table td:nth-child(9) {
    text-align: center;
    font-size: 0.75rem;
}

.custom-table th:nth-child(6),
.custom-table td:nth-child(6) {
  width: 95px; /* عرض أكبر للسعر */
  white-space: nowrap;
  text-align: center;
}

</style>

<?php require __DIR__.'/partials/header.php'; require_permission('assets.view'); ?>

<?php
$branchesStmt = $pdo->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC");
$branches = $branchesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
$count_q = "
  SELECT COUNT(*) AS total
  FROM assets a
  LEFT JOIN branches b ON b.id = a.branch_id
  WHERE 1
";
$count_params = [];
if($kw !== ''){
  $count_q .= " AND (a.name LIKE ? OR b.branch_name LIKE ?)";
  $count_params[] = "%$kw%";
  $count_params[] = "%$kw%";
}

$stmtCount = $pdo->prepare($count_q);
$stmtCount->execute($count_params);
$total_rows = $stmtCount->fetch()['total'];
$total_pages = ceil($total_rows / $perPage);
$offset = ($page - 1) * $perPage;

// جلب الصفوف الفعلية
$q = "
  SELECT 
    a.*,
    b.name AS branch_name
  FROM assets a
  LEFT JOIN branches b ON b.id = a.branch_id
  WHERE 1
";
$ps = [];
if($kw !== ''){
  $q .= " AND (a.name LIKE ? OR b.name LIKE ?)";
  $ps[] = "%$kw%";
  $ps[] = "%$kw%";
}
$q.=" ORDER BY id DESC LIMIT $perPage OFFSET $offset";

$s=$pdo->prepare($q);
$s->execute($ps);
$rows=$s->fetchAll();
//$can_edit = in_array(current_role(), ['admin','manager']);
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

<!--<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
  <h3 class="page-title">
  <span class="stat-icon">
    <i class="bi bi-building"></i>
  </span>
   الأصول
  </h3>
  <div class="d-flex flex-wrap align-items-center gap-2">
    <form class="d-flex align-items-center gap-2 mb-0" method="get" style="height:40px;">
      <input class="form-control" name="kw" placeholder="بحث بالاسم" value="<?= esc($kw) ?>" style="height:40px; min-width:200px;">
      <button class="btn btn-outline-secondary" style="height:40px;">بحث</button>
    </form>
   <?php if(has_permission('assets.print_excel')): ?>
    <a class="btn btn-outline-dark" href="export_assets_excel.php?kw=<?= urlencode($kw) ?>" style="height:40px;">
      <i class="bi bi-file-earmark-spreadsheet"></i> Excel
    </a>
   <?php endif ?>
   <?php if(has_permission('assets.print_pdf')): ?>
    <a class="btn btn-outline-dark" href="export_assets_pdf.php?kw=<?= urlencode($kw) ?>" style="height:40px;">
      <i class="bi bi-filetype-pdf"></i> PDF
    </a>
    <?php endif ?>
    <?php if(has_permission('assets.add')): ?>
      <button class="btn btn-orange d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add" style="height:40px;">
        <i class="bi bi-plus-lg me-1"></i> إضافة
      </button>
    <?php endif; ?>
  </div>
</div>-->

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
  <h3 class="page-title mb-0">
    <span class="stat-icon">
      <i class="bi bi-building"></i>
    </span>
    الأصول
  </h3>

  <div class="d-flex flex-wrap align-items-center gap-2">

    <!-- مربع البحث -->
    <form class="d-flex align-items-center gap-2 mb-0" method="get" style="height:40px;">
      <input class="form-control" name="kw" placeholder="بحث بالاسم" value="<?= esc($kw) ?>" style="height:40px; min-width:200px;">
      <button class="btn btn-outline-secondary" style="height:40px;">بحث</button>
    </form>

    <!-- تصدير Excel -->
    <?php if(has_permission('assets.print_excel')): ?>
      <a class="btn btn-outline-success d-flex align-items-center" href="export_assets_excel.php?kw=<?= urlencode($kw) ?>" style="height:40px;">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
      </a>
    <?php endif; ?>

    <!-- تصدير PDF -->
    <?php if(has_permission('assets.print_pdf')): ?>
      <a class="btn btn-outline-danger d-flex align-items-center" href="export_assets_pdf.php?kw=<?= urlencode($kw) ?>" style="height:40px;">
        <i class="bi bi-filetype-pdf me-1"></i> PDF
      </a>
    <?php endif; ?>

    <!-- زر إضافة أصل فردي -->
    <?php if(has_permission('assets.add')): ?>
      <button class="btn btn-orange d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add" style="height:40px;">
        <i class="bi bi-plus-circle me-1"></i> إضافة
      </button>
    <?php endif; ?>

    <!-- زر إضافة أصول متعددة -->
    <?php if(has_permission('assets.add_group')): ?>
      <button class="btn btn-warning d-flex align-items-center text-dark" data-bs-toggle="modal" data-bs-target="#addAsset" style="height:40px;">
        <i class="bi bi-layers me-1"></i> إضافة مجموعة
      </button>
    <?php endif; ?>

    <!-- استيراد من Excel -->
    <?php if(has_permission('assets.addAssetExcel')): ?>
      <button class="btn btn-outline-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importAssetsExcel" style="height:40px;">
        <i class="bi bi-cloud-arrow-up me-1"></i> استيراد Excel
      </button>
    <?php endif; ?>
    <?php if(has_permission('assets.downloadExcelAssets')): ?>
      <!-- 🔹 زر تحميل نموذج الأصول -->
      <a href="uploads/sample_assets.xlsx" download class="btn btn-outline-info d-flex align-items-center" style="height:40px;">
        <i class="bi bi-download me-1"></i> تحميل نموذج Excel
      </a>
    <?php endif; ?> 
  </div>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table">
    <thead class="table-light border-bottom border-2 small-header text-center text-secondary fw-semibold">
      <tr>
        <th>#</th>
        <th>الرقم التسلسلي</th>
        <th>الاسم</th>
        <th>الفرع</th>
        <th>النوع</th>
        <th>العدد</th>
        <th>السعر</th>
        <th>الضريبة (15%)</th>
        <th>الإجمالي بعد الضريبة</th>
        <th>الدافع</th>
        <th>مصدر الدفع</th>
        <th>التاريخ</th>
        <?php if(has_permission('assets.processes')): ?><th>عمليات</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
      <tr class="text-center">
        <td data-label="#" class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td data-label="رقم تسلسلي"><?= esc($r['invoice_serial']) ?></td>
        <!--<td>
          <?php if($r['image']): ?>
            <img src="uploads/<?= esc($r['image']) ?>" width="44" class="rounded">
          <?php endif; ?>
        </td>-->
        <td data-label="الاسم"><?= esc($r['name']) ?></td>
        <td data-label="الفرع">
          <?= esc($r['branch_name'] ?? '-') ?>
        </td>
        <td data-label="النوع"><?= esc($r['type']) ?></td>
        <td data-label="العدد"><?= (int)$r['quantity'] ?></td>
        <td data-label="السعر"><?= number_format((float)$r['price'],2) ?></td>
        <td data-label="الضريبة">
          <?php if (!empty($r['has_vat']) && $r['has_vat'] == 1): ?>
            <span class="text-primary fw-semibold"><?= number_format((float)$r['vat_value'],2) ?></span>
          <?php else: ?>
            <span class="text-muted small">بدون</span>
          <?php endif; ?>
        </td>
        <td data-label="الاجمالي بعد الضريبة" class="fw-bold text-dark"><?= number_format((float)$r['total_amount'],2) ?></td>
        <td data-label="الدافع"><?= esc($r['payer_name'] ?? '-') ?></td>
        <td data-label="مصدر الدفع"><?= esc($r['payment_source'] ?? '-') ?></td>
        <td data-label="التاريخ"><?= esc($r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '') ?></td>
        <?php if(has_permission('assets.processes')): ?>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionsAsset<?= $r['id'] ?>">
            <i class="bi bi-gear-fill"></i>
          </button>

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
                  <?php if(has_permission('assets.print')): ?>
                  <a class="btn btn-outline-primary w-100 mb-2" href="invoice_assest?id=<?= $r['id'] ?>">
                    <i class="bi bi-printer me-2"></i> طباعة
                  </a>
                  <?php endif ?>
                  <?php if(has_permission('assets.edit')): ?>
                  <button class="btn btn-outline-warning w-100 mb-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>">
                    <i class="bi bi-pencil me-2"></i> تعديل
                  </button>
                  <?php endif ?>
                  <?php if(has_permission('assets.delete')): ?>
                  <button class="btn btn-outline-danger w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>">
                    <i class="bi bi-trash me-2"></i> حذف
                  </button>
                  <?php endif ?>
                </div>
              </div>
            </div>
          </div>
        </td>
        <?php endif; ?>
      </tr>


    <!-- Modal تعديل -->
    <?php if(has_permission('assets.edit')): ?>
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

              <label>الفرع</label>
              <select name="branch_id" class="form-select" required>
                <option value="" hidden>اختر الفرع</option>
                <?php foreach($branches as $b): ?>
                  <option value="<?= $b['id'] ?>" <?= $r['branch_id']==$b['id']?'selected':'' ?>>
                    <?= esc($b['branch_name']) ?>
                  </option>
                <?php endforeach; ?>
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
    <?php if(has_permission('assets.delete')): ?>
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
  <ul class="pagination justify-content-center flex-wrap overflow-auto" style="gap:4px;">
    <!-- أول صفحة -->
    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=1">الأول</a>
    </li>

    <!-- السابق -->
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $page - 1 ?>">السابق</a>
    </li>

    <?php
    $max_links = 5;
    $start = max($page - 2, 1);
    $end = min($page + 2, $total_pages);

    if($start > 1){
        echo '<li class="page-item disabled"><span class="page-link px-2 py-1">…</span></li>';
    }

    for($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor;

    if($end < $total_pages){
        echo '<li class="page-item disabled"><span class="page-link px-2 py-1">…</span></li>';
    }
    ?>

    <!-- التالي -->
    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $page + 1 ?>">التالي</a>
    </li>

    <!-- آخر صفحة -->
    <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $total_pages ?>">الأخير</a>
    </li>
  </ul>
</nav>
<?php endif; ?>



<?php if(has_permission('assets.add')): ?>
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

          <div>
            <label class="form-label">الفرع</label>
            <select name="branch_id" class="form-select" required>
              <option hidden>اختر الفرع</option>
              <?php foreach($branches as $b): ?>
                <option value="<?= $b['id'] ?>"><?= esc($b['branch_name']) ?></option>
              <?php endforeach; ?>
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

<?php if(has_permission('assets.add_group')): ?>
<!-- Modal إضافة أصول يدوياً -->
<div class="modal fade" id="addAsset">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="asset_add_group" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header">
          <h5 class="modal-title">إضافة أصول متعددة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

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
            <div class="col-md-6 mb-3">
              <label>الفرع</label>
              <select name="branch_id" class="form-select" required>
                <option hidden>اختر الفرع</option>
                <?php foreach($branches as $b): ?>
                  <option value="<?= $b['id'] ?>"><?= esc($b['branch_name']) ?></option>
                <?php endforeach; ?>
              </select>
           </div>
          </div>

          <div class="table-responsive">
            <table class="odoo-table" id="assetsTable">
              <thead>
                <tr>
                  <th>رقم الفاتورة</th>
                  <th>تاريخ الفاتورة</th>
                  <th>اسم الأصل</th>
                  <th>النوع</th>
                  <th>الكمية</th>
                  <th>السعر</th>
                  <th>ضريبة (15%)</th>
                  <th>إجمالي بعد الضريبة</th>
                  <th>إزالة</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><input type="number" name="invoice_serial[]" class="form-control" required></td>
                  <td><input type="date" name="invoice_date[]" class="form-control" required></td>
                  <td><input name="name[]" class="form-control" required></td>
                  <td><input name="type[]" class="form-control" required></td>
                  <td><input type="number" step="0.001" name="quantity[]" class="form-control" required></td>
                  <td><input type="number" step="0.01" name="price[]" class="form-control" required></td>
                  <td>
                    <select name="has_vat[]" class="form-select">
                      <option value="0">بدون</option>
                      <option value="1">مع الضريبة</option>
                    </select>
                  </td>
                  <td><input type="text" name="total_amount[]" class="form-control" readonly></td>
                  <td><button type="button" class="btn btn-danger btn-sm remove-row">✖</button></td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" id="addAssetRow" class="btn btn-secondary">+ إضافة صف</button>

          <hr>

          <div class="mt-4">
            <label>صورة الفاتورة</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-receipt"></i>
              <span id="file-text-asset-main"></span>
              <input type="file" name="invoice_image" accept="image/*"
                     onchange="previewFile(this,'file-text-asset-main','preview-asset-main')">
              <img id="preview-asset-main" style="display:none; max-width:150px; margin-top:10px"/>
            </label>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" name="save" class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(has_permission('assets.addAssetExcel')): ?>
<!-- Modal استيراد أصول من Excel -->
<div class="modal fade" id="importAssetsExcel">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="asset_import_excel" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-file-earmark-spreadsheet"></i> استيراد أصول من ملف Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!--<div class="mb-3">
            <label>رقم الفاتورة</label>
            <input type="number" name="invoice_serial" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>تاريخ الفاتورة</label>
            <input type="date" name="invoice_date" class="form-control" required>
          </div>-->

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
            <div class="col-md-6 mb-3">
              <label>الفرع</label>
              <select name="branch_id" class="form-select" required>
                <option hidden>اختر الفرع</option>
                <?php foreach($branches as $b): ?>
                  <option value="<?= $b['id'] ?>"><?= esc($b['branch_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label>ملف Excel</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-cloud-arrow-up"></i>
              <span id="file-text-asset-excel">اختر ملف Excel</span>
              <input type="file" name="excel_file" accept=".xlsx,.xls" required
                     onchange="document.getElementById('file-text-asset-excel').textContent=this.files[0].name">
            </label>
          </div>

          <div class="alert alert-info">
            📘 يجب أن يحتوي ملف Excel على الأعمدة التالية:
            <ul class="mb-0">
              <li><b>invoice_serial</b> : رقم الفاتورة</li>
              <li><b>invoice_date</b> : تاريخ الفاتورة</li>
              <li><b>name</b> : اسم الأصل</li>
              <li><b>type</b> : نوع الأصل</li>
              <li><b>quantity</b> : الكمية</li>
              <li><b>price</b> : السعر</li>
              <li><b>has_vat</b> : 1 = مع الضريبة / 0 = بدون</li>
            </ul>
          </div>

          <div class="mt-4">
            <label>صورة الفاتورة (اختياري)</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-receipt"></i>
              <span id="file-text-asset-img"></span>
              <input type="file" name="invoice_image" accept="image/*"
                     onchange="previewFile(this,'file-text-asset-img','preview-asset-img')">
              <img id="preview-asset-img" style="display:none; max-width:150px; margin-top:10px"/>
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="save" class="btn btn-orange">استيراد</button>
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

<script>
document.addEventListener("DOMContentLoaded", function() {

  // زر إضافة صف جديد
  document.getElementById("addAssetRow").addEventListener("click", function() {
    const tableBody = document.querySelector("#assetsTable tbody");
    const firstRow = tableBody.querySelector("tr");
    const newRow = firstRow.cloneNode(true);

    // مسح القيم القديمة
    newRow.querySelectorAll("input").forEach(input => {
      input.value = "";
    });

    // إعادة تهيئة الأحداث للحساب والحذف
    tableBody.appendChild(newRow);
    attachEvents(newRow);
  });

  // تفعيل الحساب والحذف في الصف الأول
  document.querySelectorAll("#assetsTable tbody tr").forEach(attachEvents);

  // وظيفة لإضافة الأحداث
  function attachEvents(row) {
    const quantity = row.querySelector("input[name='quantity[]']");
    const price = row.querySelector("input[name='price[]']");
    const vat = row.querySelector("select[name='has_vat[]']");
    const total = row.querySelector("input[name='total_amount[]']");
    const removeBtn = row.querySelector(".remove-row");

    function calculate() {
      const q = parseFloat(quantity.value) || 0;
      const p = parseFloat(price.value) || 0;
      const hasVat = vat.value === "1";
      let result = q * p;
      if (hasVat) result *= 1.15; // ضريبة 15%
      total.value = result.toFixed(2);
    }

    [quantity, price, vat].forEach(el => el.addEventListener("input", calculate));

    removeBtn.addEventListener("click", function() {
      const rows = document.querySelectorAll("#assetsTable tbody tr");
      if (rows.length > 1) row.remove();
    });
  }

});
</script>

