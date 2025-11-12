<?php
require __DIR__.'/partials/header.php';
require_permission('roles.view'); ?>
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
.permissions-box {
  background: #fdfdfd;
  border-radius: 10px;
  padding: 8px;
}

.permissions-box .border {
  border-color: #e0e0e0 !important;
}

.permissions-box strong {
  font-size: 0.9rem;
}

.permissions-box label {
  cursor: pointer;
  user-select: none;
}

@media screen and (min-width: 768px) {
.permissions-box label {
  font-size: 0.8rem;
  white-space: nowrap; /* يمنع النزول لسطر جديد */
}

.permissions-box .col-6 {
  padding-right: 8px;
  padding-left: 8px;
}

.permissions-box input[type="checkbox"] {
  transform: scale(1.1);
  accent-color: #ff8800; /* نفس اللون البرتقالي */
}
.view_roles{
  margin-left:3px !important
}
.edit_roles{
  margin-left:3px !important
}
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
<div id="toast" style="
  visibility: hidden;
  min-width: 250px;
  background-color: #333;
  color: #fff;
  text-align: center;
  border-radius: 5px;
  padding: 16px;
  position: fixed;
  z-index: 9999;
  left: 50%;
  top: 20px;
  transform: translateX(-50%);
  font-size: 16px;
">
</div>
<div class="page-header mb-3">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

    <!-- العنوان -->
    <h3 class="page-title m-0 gap-2 w-100">
      <span class="stat-icon"><i class="bi bi-shield-lock"></i></span>
      الأدوار والصلاحيات
    </h3>

    <!-- البحث + الأزرار -->
    <div class="actions d-flex flex-wrap justify-content-end gap-2 w-100 w-md-auto">

      <input type="text" id="searchInput" class="form-control form-control-sm flex-grow-1"
             placeholder="بحث عن دور..." style="min-width: 180px; max-width: 220px;">

      <?php if(has_permission('roles.add')): ?>
      <button class="btn btn-orange flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#addRole">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">إضافة دور</span>
      </button>
      <?php endif ?>

      <?php if(has_permission('roles.add_group')): ?>
      <button class="btn btn-outline-danger flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#addMultipleRoles">
        <i class="bi bi-plus-circle-dotted"></i> <span class="d-none d-sm-inline">إضافة متعددة</span>
      </button>
      <?php endif ?>

    </div>

  </div>
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
        <td data-label="#" class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td data-label="اسم الدور"><?= esc($r['name']) ?></td>
        <td data-label="الوصف"><?= esc($r['description'] ?? '-') ?></td>
        <td data-label="عدد الصلاحيات"><span class="badge bg-light text-dark"><?= $count ?></span></td>
        <td data-label="تاريخ الانشاء" class="text-secondary small"><?= esc($r['created_at']) ?></td>
        <?php if(has_permission('roles.processes')): ?>
        <td>
            <?php if(has_permission('roles.view_row')): ?>
            <button class="btn btn-sm btn-info view_roles" data-bs-toggle="modal" data-bs-target="#viewPerms<?= $r['id'] ?>">
                <i style="color:#FFF" class="bi bi-eye"></i>
            </button>
            <?php endif ?>
          <?php if(has_permission('roles.edit')): ?>
          <button class="btn btn-sm btn-outline-warning edit_roles" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
          <?php endif ?>
          <?php if(has_permission('roles.delete')): ?>
          <button class="btn btn-sm btn-outline-danger delete_roles" data-bs-toggle="modal" data-bs-target="#delete<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
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
                            'permissions' => 'الصلاحيات',
                            'purchases' => 'المشتريات',
                            'orders' => 'أوامر التشغيل',
                            'custodies' => 'العهد',
                            'assets' => 'الأصول',
                            'expenses' => 'المصروفات',
                            'reports' => 'التقارير',
                        ];
                        ?>
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $group.$r['id'] ?>">
                            <i class="bi bi-folder me-2 text-orange"></i><?= $group_names[strtolower($group)] ?? $group ?>
                        </button>
                        </h2>

                        <div id="collapse<?= $group.$r['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#permAccordion<?= $r['id'] ?>">
                        <div class="accordion-body">
                            <!-- أزرار تحديد الكل / إلغاء التحديد -->
                            <div class="mb-2 text-end">
                            <button type="button" class="btn btn-sm btn-outline-success me-1 select-all" data-target="collapse<?= $group.$r['id'] ?>">تحديد الكل</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary unselect-all" data-target="collapse<?= $group.$r['id'] ?>">إلغاء الكل</button>
                            </div>

                            <div class="row">
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
                        'permissions' => 'الصلاحيات',
                        'purchases' => 'المشتريات',
                        'orders' => 'أوامر التشغيل',
                        'custodies' => 'العهد',
                        'assets' => 'الأصول',
                        'expenses' => 'المصروفات',
                        'reports' => 'التقارير',
                    ];
                    ?>
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdd<?= $group ?>">
                        <i class="bi bi-folder me-2 text-orange"></i><?= $group_names[strtolower($group)] ?? $group ?>
                    </button>
                    </h2>

                    <div id="collapseAdd<?= $group ?>" class="accordion-collapse collapse" data-bs-parent="#permAccordionAdd">
                    <div class="accordion-body">
                        <!-- أزرار تحديد الكل / إلغاء التحديد -->
                        <div class="mb-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-success me-1 select-all" data-target="collapseAdd<?= $group ?>">تحديد الكل</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary unselect-all" data-target="collapseAdd<?= $group ?>">إلغاء الكل</button>
                        </div>

                        <div class="row">
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

<?php if(has_permission('roles.add_group')): ?>
<div class="modal fade" id="addMultipleRoles">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="roles_add_multiple" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-plus-square-dotted me-1"></i> إضافة أدوار متعددة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center" id="rolesTable">
              <thead class="table-light">
                <tr>
                  <th style="width:20%">اسم الدور</th>
                  <th style="width:20%">الوصف</th>
                  <th style="width:55%">الصلاحيات</th>
                  <th style="width:5%">إجراء</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <input type="text" name="roles[0][name]" class="form-control" required placeholder="مثال: مدير النظام">
                  </td>
                  <td>
                    <input type="text" name="roles[0][description]" class="form-control" placeholder="مثال: لديه كل الصلاحيات">
                  </td>
                  <td class="text-start">
                    <div class="d-flex justify-content-between mb-2">
                      <button type="button" class="btn btn-sm btn-success select-all">اختيار الكل</button>
                      <button type="button" class="btn btn-sm btn-secondary deselect-all">مسح الكل</button>
                    </div>

                    <div class="permissions-box border rounded p-2" style="max-height:400px; overflow-y:auto; font-size:13px;">
                      <?php 
                        $groups = [];
                        foreach ($permissions as $perm) {
                          $groups[$perm['category']][] = $perm;
                        }
                        foreach ($groups as $groupName => $perms): ?>
                          <div class="mb-2 border rounded p-2 bg-light">
                            <strong class="text-primary d-block mb-1"><?= esc($groupName) ?></strong>
                            <div class="row g-2">
                              <?php foreach ($perms as $p): ?>
                                <div class="col-6"> <!-- عمودين -->
                                  <label class="form-check-label d-flex align-items-center gap-1" style="white-space: nowrap;">
                                    <input type="checkbox" class="form-check-input me-1"
                                      name="roles[0][permissions][]" value="<?= $p['id'] ?>">
                                    <span><?= esc($p['label']) ?></span>
                                  </label>
                                </div>
                              <?php endforeach; ?>
                            </div>
                          </div>
                      <?php endforeach; ?>
                    </div>
                  </td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm removeRow">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn btn-outline-primary mt-2" id="addRoleRow">
            <i class="bi bi-plus-circle"></i> إضافة صف جديد
          </button>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-orange">حفظ جميع الأدوار</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // عند الضغط على زر "تحديد الكل"
  document.querySelectorAll('.select-all').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = document.getElementById(this.dataset.target);
      target.querySelectorAll('input[type="checkbox"]').forEach(chk => chk.checked = true);
    });
  });

  // عند الضغط على زر "إلغاء الكل"
  document.querySelectorAll('.unselect-all').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = document.getElementById(this.dataset.target);
      target.querySelectorAll('input[type="checkbox"]').forEach(chk => chk.checked = false);
    });
  });
});
</script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  let index = 1;
  const tbody = document.querySelector("#rolesTable tbody");
  const templateRow = tbody.rows[0].cloneNode(true);

  document.getElementById("addRoleRow").addEventListener("click", function () {
    const newRow = templateRow.cloneNode(true);

    // تحديث أسماء الحقول
    newRow.querySelectorAll("input, textarea").forEach(el => {
      if (el.name.includes("[name]"))
        el.name = `roles[${index}][name]`;
      else if (el.name.includes("[description]"))
        el.name = `roles[${index}][description]`;
      else if (el.name.includes("[permissions]"))
        el.name = `roles[${index}][permissions][]`;

      if (el.type === "text" || el.tagName === "TEXTAREA")
        el.value = "";
      else if (el.type === "checkbox")
        el.checked = false;
    });

    tbody.appendChild(newRow);
    index++;
  });

  // حذف صف
  document.addEventListener("click", function (e) {
    if (e.target.closest(".removeRow")) {
      const rows = document.querySelectorAll("#rolesTable tbody tr");
      if (rows.length > 1) e.target.closest("tr").remove();
    }
  });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("searchInput");
  const tableRows = document.querySelectorAll("tbody tr");

  searchInput.addEventListener("keyup", function() {
    const term = this.value.toLowerCase().trim();

    tableRows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(term) ? "" : "none";
    });
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('#addMultipleRoles form');
  if (!form) return;

  const toast = document.getElementById('toast');

  function showToast(message, duration = 3000) {
    toast.textContent = message;
    toast.style.visibility = 'visible';
    toast.style.opacity = '1';
    toast.style.transition = 'opacity 0.5s';

    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.style.visibility = 'hidden', 500);
    }, duration);
  }

  form.addEventListener('submit', function (e) {
    const rows = form.querySelectorAll('#rolesTable tbody tr');
    for (const row of rows) {
      const checked = row.querySelectorAll('input[type="checkbox"]:checked');
      if (checked.length === 0) {
        e.preventDefault();
        showToast('يجب اختيار صلاحية واحدة على الأقل لكل دور قبل الحفظ.');
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // اختيار أو إلغاء الكل داخل كل صف
  document.querySelectorAll('#rolesTable').forEach(table => {
    table.addEventListener('click', function(e) {
      const btn = e.target.closest('.select-all, .deselect-all');
      if (!btn) return;

      const permissionsBox = btn.closest('td').querySelector('.permissions-box');
      if (!permissionsBox) return;

      const checkboxes = permissionsBox.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(chk => chk.checked = btn.classList.contains('select-all'));
    });
  });

  // تأكد من اختيار صلاحية واحدة على الأقل لكل صف قبل الإرسال

});
</script>
<?php require __DIR__.'/partials/footer.php'; ?>
