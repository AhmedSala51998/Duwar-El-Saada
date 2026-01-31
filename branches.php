<?php
require __DIR__ . '/partials/header.php';
require_permission('branches.view');

/* ===============================
   جلب الفروع
================================ */
$stmt = $pdo->query("
    SELECT id, name, address, phone, created_at
    FROM branches
    ORDER BY id DESC
");
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-content">

  <!-- ===== Header ===== -->
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

<!-- Edit & Delete -->
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
        <strong><?= htmlspecialchars($branch['name']) ?></strong> ؟
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
