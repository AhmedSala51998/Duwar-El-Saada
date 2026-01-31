<?php
require __DIR__ . '/partials/header.php';
require_permission('branches.view');

/* ===============================
   Pagination Setup
================================ */
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

/* ===============================
   Total Rows
================================ */
$total_stmt = $pdo->query("SELECT COUNT(*) FROM branches");
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

/* ===============================
   Fetch Branches
================================ */
$stmt = $pdo->prepare("
    SELECT id, name, address, phone, created_at
    FROM branches
    ORDER BY id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ===== CSS خاص بالجدول والPagination ===== -->
<style>
.table-responsive {
    border-radius: 0.75rem;
    background: #fff;
    padding: 10px;
    box-shadow: 0 0 8px rgba(0,0,0,0.05);
}
.custom-table {
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
}
.custom-table thead th {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    text-align: center;
}
.custom-table tbody tr:hover {
    background-color: #f1f5ff;
}
.custom-table td, .custom-table th {
    padding: 0.6rem 0.75rem;
    vertical-align: middle;
    text-align: center;
}
.pagination .page-link {
    color: #0d6efd;
    border-color: #0d6efd;
}
.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}
.pagination .page-link:hover {
    background-color: #0d6efd;
    color: #fff;
}
</style>

<div class="page-content">

  <!-- ===== Page Header ===== -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="page-title mb-0">
      <span class="stat-icon">
        <i class="bi bi-diagram-3"></i>
      </span>
      الفروع
    </h3>

    <?php if(has_permission('branches.create')): ?>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
        <i class="bi bi-plus-circle"></i> إضافة فرع
      </button>
    <?php endif ?>
  </div>

  <!-- ===== Table ===== -->
  <div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
    <table class="table table-hover align-middle mb-0 custom-table">
      <thead class="table-light border-bottom border-2 small-header text-center text-secondary fw-semibold">
        <tr>
          <th>#</th>
          <th>اسم الفرع</th>
          <th>العنوان</th>
          <th>الهاتف</th>
          <th>تاريخ الإضافة</th>
          <th>الإجراءات</th>
        </tr>
      </thead>

      <tbody class="text-center">
        <?php if(empty($branches)): ?>
          <tr>
            <td colspan="6" class="text-muted py-4">لا يوجد فروع</td>
          </tr>
        <?php endif; ?>

        <?php foreach($branches as $branch): ?>
        <tr>
          <td><?= $branch['id'] ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($branch['name']) ?></td>
          <td><?= htmlspecialchars($branch['address']) ?></td>
          <td><?= htmlspecialchars($branch['phone']) ?></td>
          <td><?= date('Y-m-d', strtotime($branch['created_at'])) ?></td>
          <td>
            <?php if(has_permission('branches.edit')): ?>
              <button class="btn btn-sm btn-outline-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#editBranch<?= $branch['id'] ?>">
                <i class="bi bi-pencil-square"></i>
              </button>
            <?php endif ?>

            <?php if(has_permission('branches.delete')): ?>
              <button class="btn btn-sm btn-outline-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteBranch<?= $branch['id'] ?>">
                <i class="bi bi-trash"></i>
              </button>
            <?php endif ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Pagination ===== -->
  <?php if($total_pages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination justify-content-center mb-0">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page-1 ?>">السابق</a>
      </li>

      <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page+1 ?>">التالي</a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>

</div>

<!-- ===============================
     Modals
================================ -->

<!-- Add Branch -->
<?php if(has_permission('branches.create')): ?>
<div class="modal fade" id="addBranchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="branch_add.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">إضافة فرع</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">اسم الفرع</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">العنوان</label>
          <input type="text" name="address" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">الهاتف</label>
          <input type="text" name="phone" class="form-control">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">حفظ</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Edit & Delete Modals -->
<?php foreach($branches as $branch): ?>

<?php if(has_permission('branches.edit')): ?>
<div class="modal fade" id="editBranch<?= $branch['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="branch_edit.php" class="modal-content">
      <input type="hidden" name="id" value="<?= $branch['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title">تعديل فرع</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">اسم الفرع</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($branch['name']) ?>" required>
        </div>
        <div class="mb-2">
          <label class="form-label">العنوان</label>
          <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($branch['address']) ?>">
        </div>
        <div class="mb-2">
          <label class="form-label">الهاتف</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($branch['phone']) ?>">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-warning">تحديث</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if(has_permission('branches.delete')): ?>
<div class="modal fade" id="deleteBranch<?= $branch['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="branch_delete.php" class="modal-content">
      <input type="hidden" name="id" value="<?= $branch['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title text-danger">حذف فرع</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        هل أنت متأكد من حذف فرع
        <strong><?= htmlspecialchars($branch['name']) ?></strong>؟
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">حذف</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php endforeach; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
