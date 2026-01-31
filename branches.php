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
<?php
require __DIR__.'/partials/header.php';
require_permission('branches.view');

$kw = trim($_GET['kw'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

/* count */
$count = $pdo->prepare("
  SELECT COUNT(*) FROM branches
  WHERE branch_name LIKE ? OR phone LIKE ?
");
$count->execute(["%$kw%", "%$kw%"]);
$total_rows = $count->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

/* data */
$stmt = $pdo->prepare("
  SELECT * FROM branches
  WHERE branch_name LIKE ? OR phone LIKE ?
  ORDER BY id DESC
  LIMIT $per_page OFFSET $offset
");
$stmt->execute(["%$kw%", "%$kw%"]);
$rows = $stmt->fetchAll();
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
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">

  <!-- العنوان -->
  <h3 class="page-title mb-2 mb-md-0 d-flex align-items-center">
    <span class="stat-icon me-2"><i class="bi bi-diagram-3"></i></span>
    الفروع
  </h3>

  <!-- البحث + زر الإضافة -->
  <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">

    <!-- فورم البحث -->
    <form method="get" class="d-flex gap-2 flex-grow-1">
      <input type="text" name="kw" class="form-control" placeholder="بحث عن فرع بالاسم..." value="<?= esc($kw) ?>">
      <button class="btn btn-orange"><i class="bi bi-search"></i></button>
    </form>

    <!-- زر الإضافة -->
    <?php if(has_permission('branches.add')): ?>
      <button class="btn btn-orange flex-shrink-0">
        <i class="bi bi-plus-lg me-1"></i> فرع
      </button>
    <?php endif; ?>

  </div>
</div>
<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table text-center">
    <thead class="table-light border-bottom border-2 small-header text-secondary fw-semibold">
      <tr>
        <th>#</th>
        <th>اسم الفرع</th>
        <th>العنوان</th>
        <th>رقم الجوال</th>
        <th>تاريخ الإنشاء</th>
        <?php if(has_permission('branches.processes')): ?><th>عمليات</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $r): ?>
    <tr class="text-center">
    <td data-label="رقم الفرع" class="fw-bold text-muted"><?= $r['id'] ?></td>
    <td data-label="اسم الفرع"><?= esc($r['branch_name']) ?></td>
    <td data-label="عنوان الفرع"><?= esc($r['address']) ?></td>
    <td data-label="رقم الجوال"><?= esc($r['phone']) ?></td>
    <td data-label="تاريخ الاضافة"><?= esc($r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '') ?></td>
    <?php if(has_permission('branches.processes')): ?>
    <td class="text-center">
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionsBranch<?= $r['id'] ?>">
        <i class="bi bi-gear-fill"></i>
        </button>

        <div class="modal fade" id="actionsBranch<?= $r['id'] ?>" tabindex="-1" aria-labelledby="actionsBranchLabel<?= $r['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="actionsBranchLabel<?= $r['id'] ?>">
                <i class="bi bi-gear-fill me-1"></i> العمليات
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center">
                <?php if(has_permission('branches.edit')): ?>
                <button class="btn btn-outline-warning w-100 mb-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>">
                <i class="bi bi-pencil me-2"></i> تعديل
                </button>
                <?php endif; ?>
                <?php if(has_permission('branches.delete')): ?>
                <button class="btn btn-outline-danger w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>">
                <i class="bi bi-trash me-2"></i> حذف
                </button>
                <?php endif; ?>
            </div>
            </div>
        </div>
        </div>

    </td>
    <?php endif; ?>
    </tr>
<!-- مودال تعديل فرع -->
<div class="modal fade" id="edit<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="branch_edit">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-pencil-square"></i> تعديل فرع
          </h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">اسم الفرع</label>
            <input name="branch_name" class="form-control"
                   value="<?= esc($r['branch_name']) ?>" required>
          </div>

          <div>
            <label class="form-label">العنوان بالتفصيل</label>
            <textarea name="address" class="form-control" rows="3" required><?= esc($r['address']) ?></textarea>
          </div>

          <div>
            <label class="form-label">رقم الجوال</label>
            <input name="phone" class="form-control"
                   value="<?= esc($r['phone']) ?>" required>
            <small class="text-muted">صيغة سعودي: 05xxxxxxxx</small>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-orange">حفظ التعديلات</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- مودال حذف فرع -->
<div class="modal fade" id="del<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="get" action="branch_delete">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">

        <div class="modal-header">
          <h5 class="modal-title text-danger">
            <i class="bi bi-exclamation-triangle"></i> تأكيد الحذف
          </h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">
          <p>
            هل أنت متأكد من حذف الفرع
            <strong><?= esc($r['branch_name']) ?></strong> ؟
          </p>
          <p class="text-muted small">
            لا يمكن الحذف إذا كان الفرع مرتبط بمصروفات أو مشتريات أو أصول أو عهد.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            إلغاء
          </button>
          <button type="submit" class="btn btn-danger">
            حذف
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if ($total_pages > 1): ?>
<nav aria-label="صفحات المستخدمين" class="mt-3">
  <ul class="pagination justify-content-center flex-wrap overflow-auto" style="gap:4px;">
    <!-- أول صفحة -->
    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw ?? '') ?>&page=1">الأول</a>
    </li>

    <!-- السابق -->
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw ?? '') ?>&page=<?= $page - 1 ?>">السابق</a>
    </li>

    <?php
    $max_links = 5;
    $start = max($page - 2, 1);
    $end = min($page + 2, $total_pages);

    if ($start > 1) {
      echo '<li class="page-item disabled"><span class="page-link px-2 py-1">…</span></li>';
    }

    for ($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor;

    if ($end < $total_pages) {
      echo '<li class="page-item disabled"><span class="page-link px-2 py-1">…</span></li>';
    }
    ?>

    <!-- التالي -->
    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw ?? '') ?>&page=<?= $page + 1 ?>">التالي</a>
    </li>

    <!-- آخر صفحة -->
    <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw ?? '') ?>&page=<?= $total_pages ?>">الأخير</a>
    </li>
  </ul>
</nav>
<?php endif; ?>
<div class="modal fade" id="addBranch">
<div class="modal-dialog">
<div class="modal-content">
<form method="post" action="branch_add">
<input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

<div class="modal-header">
  <h5>إضافة فرع</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body vstack gap-3">
  <input name="branch_name" class="form-control" placeholder="اسم الفرع" required>
  <textarea name="address" class="form-control" placeholder="العنوان بالتفصيل" required></textarea>
  <input name="phone" class="form-control" placeholder="05xxxxxxxx" required>
</div>

<div class="modal-footer">
  <button class="btn btn-orange">حفظ</button>
</div>
</form>
</div>
</div>
</div>
<?php require __DIR__.'/partials/footer.php'; ?>