<?php
require __DIR__.'/partials/header.php';
require_role('admin');?>
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
// جلب الصلاحيات
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY code ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title">
    <span class="stat-icon"><i class="bi bi-lock"></i></span>
    إدارة الصلاحيات
  </h3>
  <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addPerm"><i class="bi bi-plus-lg"></i> إضافة صلاحية</button>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table custom-table table-hover align-middle mb-0 text-center">
    <thead class="table-light border-bottom small-header text-secondary">
      <tr>
        <th>#</th>
        <th>الكود</th>
        <th>الاسم الظاهر</th>
        <th>الوصف</th>
        <th>عمليات</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($permissions as $p): ?>
      <tr>
        <td class="fw-bold text-muted"><?= $p['id'] ?></td>
        <td><code class="text-orange"><?= esc($p['code']) ?></code></td>
        <td><?= esc($p['label']) ?></td>
        <td><?= esc($p['description'] ?? '-') ?></td>
        <td>
          <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $p['id'] ?>"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $p['id'] ?>"><i class="bi bi-trash"></i></button>
        </td>
      </tr>

      <!-- مودال تعديل -->
      <div class="modal fade" id="edit<?= $p['id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="permission_edit">
              <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> تعديل الصلاحية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body vstack gap-3">
                <div>
                  <label class="form-label">الكود (code)</label>
                  <input name="code" class="form-control" value="<?= esc($p['code']) ?>" required>
                </div>
                <div>
                  <label class="form-label">الاسم الظاهر (label)</label>
                  <input name="label" class="form-control" value="<?= esc($p['label']) ?>" required>
                </div>
                <div>
                  <label class="form-label">الوصف</label>
                  <textarea name="description" class="form-control" rows="2"><?= esc($p['description']) ?></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-orange">حفظ التعديلات</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- مودال حذف -->
      <div class="modal fade" id="delete<?= $p['id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="get" action="permission_delete">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-trash me-1"></i> تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                هل أنت متأكد من حذف الصلاحية <strong><?= esc($p['label']) ?></strong>؟
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button class="btn btn-danger">حذف</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- مودال إضافة -->
<div class="modal fade" id="addPerm">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="permission_add">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-plus-lg me-1"></i> إضافة صلاحية جديدة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">الكود (code)</label>
            <input name="code" class="form-control" placeholder="مثال: users.create" required>
          </div>
          <div>
            <label class="form-label">الاسم الظاهر (label)</label>
            <input name="label" class="form-control" placeholder="مثال: إنشاء مستخدم" required>
          </div>
          <div>
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="2" placeholder="اختياري..."></textarea>
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
