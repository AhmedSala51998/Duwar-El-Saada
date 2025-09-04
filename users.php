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
<?php endif; ?>
<?php $rows=$pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">المستخدمون</h3>
  <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> مستخدم</button>
</div>

<table class="table table-hover">
  <thead class="table-light">
    <tr>
      <th>#</th><th>اسم المستخدم</th><th>الدور</th><th>إنشاء</th><th>عمليات</th>
    </tr>
  </thead>
  <tbody>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= esc($r['username']) ?></td>
  <td><?= esc($r['role']) ?></td>
  <td><?= esc($r['created_at']) ?></td>
  <td>
    <!-- زر التعديل -->
    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>">
      <i class="bi bi-pencil"></i>
    </button>
    <!-- زر الحذف -->
    <?php if($r['id']!=$_SESSION['user_id']): ?>
      <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#d<?= $r['id'] ?>">
        <i class="bi bi-trash"></i>
      </button>
    <?php endif; ?>
  </td>
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
