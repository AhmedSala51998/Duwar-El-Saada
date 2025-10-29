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
  .custom-file-upload {
    border: 2px dashed #ccc;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease-in-out;
    background: #f9f9f9;
  }
  .custom-file-upload:hover {
    border-color: #0d6efd;
    background: #eef5ff;
  }
  .custom-file-upload i {
    font-size: 40px;
    color: #0d6efd;
    margin-bottom: 10px;
  }
  .custom-file-upload span {
    font-size: 14px;
    color: #666;
  }
  .custom-file-upload img {
    max-height: 120px;
    margin-top: 10px;
    border-radius: 8px;
  }
  input[type="file"] {
    display: none;
  }
  /* لجعل الصور ثابتة الحجم في الجدول */
/* لجعل الصور في الجدول بحجم ثابت وصندوق موحد */
.table td label.custom-file-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 130px;        /* عرض الصندوق */
  height: 100px;       /* ارتفاع الصندوق */
  border: 1px dashed #ccc;
  padding: 5px;
  cursor: pointer;
  overflow: hidden;
  box-sizing: border-box;
  border-radius: 8px;  /* اختياري: حواف مدورة */
  background-color: #f9f9f9;
}

/* الصورة نفسها */
.table td label.custom-file-upload img {
  width: 100%;
  height: 100%;
  object-fit: contain; /* يجعل الصورة تظهر كاملة داخل الصندوق بدون قص */
  border-radius: 4px;
}

/* أيقونة ونص قبل رفع الصورة */
.table td label.custom-file-upload i,
.table td label.custom-file-upload span {
  position: absolute;   /* تظهر فوق الصورة قبل الاختيار */
  pointer-events: none; /* لا تمنع النقر على input */
}

/* إخفاء النص والأيقونة بعد رفع الصورة */
.table td label.custom-file-upload img[src] ~ i,
.table td label.custom-file-upload img[src] ~ span {
  display: none;
}
/* ألوان pagination مخصصة */
.pagination .page-link {
    color: #ff6a00;
    border-color: #ff6a00;
}

.pagination .page-item.active .page-link {
    background-color: #ff6a00;
    border-color: #ff6a00;
    color: #fff;
}

.pagination .page-link:hover {
    background-color: #ff6a00;
    color: #fff;
    border-color: #ff6a00;
}

.pagination .page-item.disabled .page-link {
    color: #ccc;
    border-color: #ccc;
}


.custom-table {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
}

.custom-table thead th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
  vertical-align: middle;
  font-size: 0.85rem;
  white-space: nowrap;
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
  white-space: normal !important;
  word-break: break-word;
  vertical-align: top;
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
        <th>عمليات</th>
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
        <td>
          <!-- زر التعديل -->
          <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>">
            <i class="bi bi-pencil"></i>
          </button>

          <!-- زر الحذف -->
          <?php if($r['id']!=$_SESSION['user_id']): ?>
          <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#d<?= $r['id'] ?>">
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
