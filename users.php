<?php require __DIR__.'/partials/header.php'; require_role('admin'); ?>
<?php if(!empty($_SESSION['toast'])): $toast=$_SESSION['toast']; unset($_SESSION['toast']); ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="liveToast" class="toast align-items-center text-bg-<?= $toast['type'] ?> border-0 show fade">
    <div class="d-flex"><div class="toast-body"><?= esc($toast['msg']) ?></div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded",()=>{let el=document.getElementById("liveToast");if(el){new bootstrap.Toast(el,{delay:2500}).show();}});
</script>
<style>
.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 10px;
  overflow: hidden;
  background-color: #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  direction: rtl;
}

.custom-table thead {
  background-color: #f8f9fa;
}

.custom-table th {
  font-weight: 700;
  color: #212529;
  border-bottom: 2px solid #e9ecef;
  padding: 12px;
  text-align: right;
  white-space: nowrap;
}

.custom-table td {
  border-bottom: 1px solid #f0f0f0;
  padding: 10px;
  text-align: right;
  color: #444;
  background-color: #fff;
  transition: background-color 0.2s ease;
}

.custom-table tr:hover td {
  background-color: #f9fafb;
}

.custom-table button {
  border-radius: 8px;
}

</style>
<?php endif; ?>
<?php $rows=$pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(); $can_edit = in_array(current_role(), ['admin','manager']); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">المستخدمون</h3>
  <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> مستخدم</button>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table">
    <thead class="table-light border-bottom border-2 small-header text-center text-secondary fw-semibold">
      <tr>
        <th>#</th>
        <th>اسم المستخدم</th>
        <th>الدور</th>
        <th>تاريخ الإنشاء</th>
        <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php foreach($rows as $r): ?>
      <tr>
        <td class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td><?= esc($r['username']) ?></td>
        <td>
          <span class="badge bg-light text-dark border fw-semibold px-3 py-2">
            <i class="bi bi-person-badge me-1"></i> <?= esc($r['role']) ?>
          </span>
        </td>
        <td class="text-secondary small"><?= esc($r['created_at']) ?></td>
        <?php if($can_edit): ?>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionsUser<?= $r['id'] ?>">
            <i class="bi bi-gear-fill"></i>
          </button>

          <div class="modal fade" id="actionsUser<?= $r['id'] ?>" tabindex="-1" aria-labelledby="actionsUserLabel<?= $r['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="actionsUserLabel<?= $r['id'] ?>">
                    <i class="bi bi-gear-fill me-1"></i> العمليات
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">
                  <button class="btn btn-outline-warning w-100 mb-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>">
                    <i class="bi bi-pencil me-2"></i> تعديل
                  </button>
                  <?php if($r['id'] != $_SESSION['user_id']): ?>
                  <button class="btn btn-outline-danger w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#d<?= $r['id'] ?>">
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

<!-- مودال تعديل -->
<div class="modal fade" id="e<?= $r['id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="user_edit">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">تعديل مستخدم</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">اسم المستخدم</label>
            <input name="username" class="form-control" value="<?= esc($r['username']) ?>" required>
          </div>
          <div>
            <label class="form-label">الدور</label>
            <select name="role" class="form-select">
              <?php foreach(['admin','manager','staff'] as $ro): ?>
                <option <?= $r['role']===$ro?'selected':'' ?>><?= $ro ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">كلمة مرور جديدة (اختياري)</label>
            <input type="password" name="password" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- مودال حذف -->
<div class="modal fade" id="d<?= $r['id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="get" action="user_delete">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">تأكيد الحذف</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>هل أنت متأكد من حذف المستخدم <strong><?= esc($r['username']) ?></strong>؟</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-danger">حذف</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php endforeach; ?>
  </tbody>
</table>

<!-- مودال إضافة -->
<div class="modal fade" id="add">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="user_add">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">مستخدم جديد</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">اسم المستخدم</label>
            <input name="username" class="form-control" required>
          </div>
          <div>
            <label class="form-label">كلمة المرور</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div>
            <label class="form-label">الدور</label>
            <select name="role" class="form-select">
              <option>admin</option>
              <option>manager</option>
              <option>staff</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>
