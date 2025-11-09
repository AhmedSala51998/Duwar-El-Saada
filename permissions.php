<?php
require __DIR__.'/partials/header.php';
require_permission('permissions.view');?>
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
<style>
@media (max-width: 767.98px) {
  .page-header .d-flex {
    gap: 1rem !important;
  }

  .page-header .page-title {
    text-align: center;
    font-size: 1.1rem;
    width: 100%;
  }

  .page-header .actions {
    justify-content: center !important;
  }

  .page-header .actions input {
    width: 100% !important;
    max-width: none !important;
  }

  .page-header .actions .btn {
    flex: 1 1 48% !important;
    min-width: 130px;
  }
}
@media (max-width: 767.98px) {
  /* خلية العمليات */
  td:last-child {
    display: flex;
    justify-content: center;
    gap: 0.5rem; /* المسافة بين الأزرار */
  }

  /* الأزرار */
  td:last-child .btn {
    flex: 1 1 45%;
    min-width: 100px;
  }
}
/* 📱 تحسين عرض جدول صلاحيات المجموعات في الموبايل */
@media screen and (max-width: 768px) {
  #addPermGroup .modal-dialog {
    margin: 10px;
  }

  #addPermGroup .modal-body {
    padding: 10px;
  }

  /* جعل الجدول قابل للتمرير */
  #addPermGroup .table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch; /* تمرير سلس */
  }

  #addPermGroup table {
    min-width: 700px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
    font-size: 12px; /* تصغير النص قليلاً للموبايل */
  }

  #addPermGroup th,
  #addPermGroup td {
    white-space: nowrap;
    padding: 6px 8px;
    vertical-align: middle;
  }

  #addPermGroup .btn {
    font-size: 12px;
    padding: 5px 10px;
  }

  /* تحسين المسافات حول زر إضافة صف */
  #addPermGroup .text-end.mt-3 {
    text-align: center !important;
    margin-top: 15px !important;
  }
}
</style>

<?php endif; ?>
<?php
// جلب الصلاحيات
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY code ASC")->fetchAll();
?>

<div class="page-header mb-3">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

    <!-- العنوان -->
    <h3 class="page-title m-0 gap-2 w-100">
      <span class="stat-icon"><i class="bi bi-lock"></i></span>
      إدارة الصلاحيات
    </h3>

    <!-- البحث + الأزرار -->
    <div class="actions d-flex flex-wrap justify-content-end gap-2 w-100 w-md-auto">

      <input type="text" id="searchInput" class="form-control form-control-sm flex-grow-1"
             placeholder="بحث عن صلاحية..." style="min-width: 180px; max-width: 220px;">

      <?php if(has_permission('permissions.add')): ?>
      <button class="btn btn-orange flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#addPerm">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">إضافة صلاحية</span>
      </button>
      <?php endif ?>

      <?php if(has_permission('permissions.add_group')): ?>
      <button class="btn btn-outline-danger flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#addPermGroup">
        <i class="bi bi-plus-square-dotted"></i> <span class="d-none d-sm-inline">إضافة مجموعة صلاحيات</span>
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
        <th>الكود</th>
        <th>الاسم الظاهر</th>
        <th>الوصف</th>
        <?php if(has_permission('permissions.processes')): ?><th>عمليات</th><?php endif ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($permissions as $p): ?>
      <tr>
        <td data-label="#" class="fw-bold text-muted"><?= $p['id'] ?></td>
        <td data-label="الكود"><code class="text-orange"><?= esc($p['code']) ?></code></td>
        <td data-label="الاسم الظاهر"><?= esc($p['label']) ?></td>
        <td data-label="الوصف"><?= esc($p['description'] ?? '-') ?></td>
        <?php if(has_permission('permissions.processes')): ?>
        <td>
          <?php if(has_permission('permissions.edit')): ?>
          <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $p['id'] ?>"><i class="bi bi-pencil"></i></button>
          <?php endif ?>
          <?php if(has_permission('permissions.delete')): ?>
          <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $p['id'] ?>"><i class="bi bi-trash"></i></button>
          <?php endif ?>
        </td>
        <?php endif ?>
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

<!-- مودال إضافة مجموعة صلاحيات -->
<div class="modal fade" id="addPermGroup">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="permission_add_group">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-plus-square-dotted me-1"></i> إضافة مجموعة صلاحيات</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p class="small text-muted mb-3">
            يمكنك إضافة عدة صلاحيات دفعة واحدة. استخدم زر <strong>+</strong> اضافة صف جديد.
          </p>

          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0" id="permTable">
              <thead class="table-light">
                <tr>
                  <th style="width:25%">الكود (code)</th>
                  <th style="width:25%">الاسم الظاهر (label)</th>
                  <th style="width:40%">الوصف</th>
                  <th style="width:10%">إزالة</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><input type="text" name="codes[]" class="form-control" placeholder="مثال: users.create" required></td>
                  <td><input type="text" name="labels[]" class="form-control" placeholder="مثال: إنشاء مستخدم" required></td>
                  <td><input type="text" name="descriptions[]" class="form-control" placeholder="اختياري..."></td>
                  <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="bi bi-x-lg"></i></button></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="text-end mt-3">
            <button type="button" class="btn btn-sm btn-outline-success" id="addRow"><i class="bi bi-plus-lg"></i> إضافة صف جديد</button>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-orange">حفظ جميع الصلاحيات</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const addRowBtn = document.getElementById('addRow');
  const permTable = document.getElementById('permTable').querySelector('tbody');

  addRowBtn.addEventListener('click', () => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td><input type="text" name="codes[]" class="form-control" placeholder="مثال: users.edit" required></td>
      <td><input type="text" name="labels[]" class="form-control" placeholder="مثال: تعديل مستخدم" required></td>
      <td><input type="text" name="descriptions[]" class="form-control" placeholder="اختياري..."></td>
      <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="bi bi-x-lg"></i></button></td>
    `;
    permTable.appendChild(row);
  });

  permTable.addEventListener('click', function(e) {
    if(e.target.closest('.remove-row')) {
      const row = e.target.closest('tr');
      if(permTable.rows.length > 1) row.remove();
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
<?php require __DIR__.'/partials/footer.php'; ?>
