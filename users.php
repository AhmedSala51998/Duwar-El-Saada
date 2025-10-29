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
</style>
<?php endif; ?>
<?php $rows=$pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(); ?>
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
