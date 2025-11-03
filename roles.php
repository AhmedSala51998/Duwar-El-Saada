<?php
require __DIR__.'/partials/header.php';
require_role('admin'); require_permission('roles.view'); ?>
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
// جلب الأدوار والصلاحيات
$roles = $pdo->query("SELECT * FROM roles ORDER BY id DESC")->fetchAll();
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY id")->fetchAll();

// ترتيب الصلاحيات في مجموعات (اختياري للتنظيم)
$grouped_perms = [];
foreach ($permissions as $p) {
  $group = explode('.', $p['code'])[0];
  $grouped_perms[$group][] = $p;
}
?>
<style>
  /* ✅ خلي لون الـ checkbox برتقالي عند التحديد */
  .form-check-input:checked {
    background-color: #ff8800;
    border-color: #ff8800;
  }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('form[action="role_add"], form[action="role_edit"]').forEach(form => {
    form.addEventListener('submit', function(e) {
      const formData = new FormData(form);
      const permissions = formData.getAll('permissions[]'); // جلب كل القيم المختارة

      if (permissions.length === 0) {
        e.preventDefault(); // امنع الإرسال

        // إنشاء Toast ديناميكي
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = 2000;

        toastContainer.innerHTML = `
          <div class="toast align-items-center text-bg-warning border-0 show fade" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body">
                يجب اختيار صلاحية واحدة على الأقل قبل الحفظ.
              </div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
          </div>
        `;

        document.body.appendChild(toastContainer);

        const toastEl = toastContainer.querySelector('.toast');
        const bsToast = new bootstrap.Toast(toastEl, { delay: 2500 });
        bsToast.show();

        // إزالة الـ container بعد انتهاء Toast
        toastEl.addEventListener('hidden.bs.toast', () => toastContainer.remove());
      }
    });
  });
});
</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title">
    <span class="stat-icon"><i class="bi bi-shield-lock"></i></span>
    الأدوار والصلاحيات
  </h3>
  <?php if(has_permission('roles.add')): ?>
  <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addRole"><i class="bi bi-plus-lg"></i> إضافة دور</button>
  <?php endif ?>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table custom-table table-hover align-middle mb-0 text-center">
    <thead class="table-light border-bottom small-header text-secondary">
      <tr>
        <th>#</th>
        <th>اسم الدور</th>
        <th>الوصف</th>
        <th>عدد الصلاحيات</th>
        <th>تاريخ الإنشاء</th>
        <?php if(has_permission('roles.processes')): ?><th>عمليات</th><?php endif ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($roles as $r): 
        $count = $pdo->query("SELECT COUNT(*) FROM role_permissions WHERE role_id={$r['id']}")->fetchColumn();
      ?>
      <tr>
        <td class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td><?= esc($r['name']) ?></td>
        <td><?= esc($r['description'] ?? '-') ?></td>
        <td><span class="badge bg-light text-dark"><?= $count ?></span></td>
        <td class="text-secondary small"><?= esc($r['created_at']) ?></td>
        <?php if(has_permission('roles.processes')): ?>
        <td>
            <?php if(has_permission('roles.view_row')): ?>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewPerms<?= $r['id'] ?>">
                <i style="color:#FFF" class="bi bi-eye"></i>
            </button>
            <?php endif ?>
          <?php if(has_permission('roles.edit')): ?>
          <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
          <?php endif ?>
          <?php if(has_permission('roles.delete')): ?>
          <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
          <?php endif ?>
        </td>
        <?php endif ?>
      </tr>

      <!-- مودال تعديل -->
      <?php
      $role_perms = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id=?");
      $role_perms->execute([$r['id']]);
      $role_perms = array_column($role_perms->fetchAll(), 'permission_id');
      ?>
      <div class="modal fade" id="edit<?= $r['id'] ?>">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <form method="post" action="role_edit">
              <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">

              <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> تعديل الدور</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body vstack gap-3">
                <div>
                  <label class="form-label">اسم الدور</label>
                  <input name="name" class="form-control" value="<?= esc($r['name']) ?>" required>
                </div>
                <div>
                  <label class="form-label">الوصف</label>
                  <textarea name="description" class="form-control" rows="2"><?= esc($r['description']) ?></textarea>
                </div>

                <div>
                  <label class="form-label fw-semibold text-orange">الصلاحيات</label>
                  <div class="accordion" id="permAccordion<?= $r['id'] ?>">
                    <?php foreach($grouped_perms as $group => $items): ?>
                      <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $group.$r['id'] ?>">
                            <?php
                            $group_names = [
                            'users' => 'المستخدمين',
                            'roles' => 'الأدوار',
                            'settings' => 'الإعدادات',
                            'permissions' => 'الصلاحيات'
                            ];
                            ?>
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $group.$r['id'] ?>">
                            <i class="bi bi-folder me-2 text-orange"></i><?= $group_names[strtolower($group)] ?? $group ?>
                          </button>
                        </h2>
                        <div id="collapse<?= $group.$r['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#permAccordion<?= $r['id'] ?>">
                          <div class="accordion-body row">
                            <?php foreach($items as $p): ?>
                              <div class="col-md-6 mb-1">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                                    id="perm<?= $r['id'].'_'.$p['id'] ?>"
                                    <?= in_array($p['id'], $role_perms) ? 'checked' : '' ?>>
                                  <label class="form-check-label" for="perm<?= $r['id'].'_'.$p['id'] ?>"><?= esc($p['label']) ?></label>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
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
      <div class="modal fade" id="delete<?= $r['id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="get" action="role_delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-trash me-1"></i> تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                هل تريد حذف الدور <strong><?= esc($r['name']) ?></strong>؟
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button class="btn btn-danger">حذف</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <!-- مودال عرض الصلاحيات -->
    <div class="modal fade" id="viewPerms<?= $r['id'] ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header bg-light">
            <h5 class="modal-title">صلاحيات الدور: <?= esc($r['name']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <?php
            // جلب صلاحيات الدور
            $stmt = $pdo->prepare("
            SELECT p.code, p.label
            FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = ?
            ORDER BY p.code ASC
            ");
            $stmt->execute([$r['id']]);
            $role_perms_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if($role_perms_list): ?>
            <ul class="list-group">
                <?php foreach($role_perms_list as $perm): ?>
                <li class="list-group-item"><code class="text-orange"><?= esc($perm['code']) ?></code> - <?= esc($perm['label']) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-warning">لا توجد صلاحيات لهذا الدور.</p>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        </div>
        </div>
    </div>
    </div>

      <?php endforeach; ?>
    </tbody>
  </table>
</div>


<!-- مودال إضافة -->
<div class="modal fade" id="addRole">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="role_add">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-plus-lg me-1"></i> إضافة دور جديد</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">اسم الدور</label>
            <input name="name" class="form-control" required>
          </div>
          <div>
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div>
            <label class="form-label fw-semibold text-orange">الصلاحيات</label>
            <div class="accordion" id="permAccordionAdd">
              <?php foreach($grouped_perms as $group => $items): ?>
                <div class="accordion-item">
                  <h2 class="accordion-header" id="headingAdd<?= $group ?>">
                    <?php
                    $group_names = [
                    'users' => 'المستخدمين',
                    'roles' => 'الأدوار',
                    'settings' => 'الإعدادات',
                    'permissions' => 'الصلاحيات'
                    ];
                    ?>
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdd<?= $group ?>">
                      <i class="bi bi-folder me-2 text-orange"></i><?= $group_names[strtolower($group)] ?? $group ?>
                    </button>
                  </h2>
                  <div id="collapseAdd<?= $group ?>" class="accordion-collapse collapse" data-bs-parent="#permAccordionAdd">
                    <div class="accordion-body row">
                      <?php foreach($items as $p): ?>
                        <div class="col-md-6 mb-1">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="addperm<?= $p['id'] ?>">
                            <label class="form-check-label" for="addperm<?= $p['id'] ?>"><?= esc($p['label']) ?></label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
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
