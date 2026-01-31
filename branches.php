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
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title">
    <span class="stat-icon"><i class="bi bi-diagram-3"></i></span>
    الفروع
  </h3>

  <div class="d-flex gap-2">
    <form method="get" class="d-flex gap-2">
      <input type="text" name="kw" class="form-control"
             placeholder="بحث عن فرع أو رقم..."
             value="<?= esc($kw) ?>">
      <button class="btn btn-orange"><i class="bi bi-search"></i></button>
    </form>

    <?php if(has_permission('branches.add')): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addBranch">
        <i class="bi bi-plus-lg"></i> فرع
      </button>
    <?php endif; ?>
  </div>
</div>
<div class="table-responsive bg-white rounded shadow-sm">
<table class="table custom-table align-middle text-center">
<thead>
<tr>
  <th>#</th>
  <th>اسم الفرع</th>
  <th>العنوان</th>
  <th>رقم الجوال</th>
  <th>تاريخ الإنشاء</th>
  <th>عمليات</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= esc($r['branch_name']) ?></td>
  <td><?= esc($r['address']) ?></td>
  <td><?= esc($r['phone']) ?></td>
  <td class="small text-muted"><?= $r['created_at'] ?></td>
  <td>
    <button class="btn btn-sm btn-outline-warning"
            data-bs-toggle="modal"
            data-bs-target="#edit<?= $r['id'] ?>">
      <i class="bi bi-pencil"></i>
    </button>

    <button class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#del<?= $r['id'] ?>">
      <i class="bi bi-trash"></i>
    </button>
  </td>
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