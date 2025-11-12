<?php require __DIR__.'/partials/header.php'; require_permission('users.view'); ?>
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
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem; /* تصغير النص قليلاً للراحة البصرية */
}

.custom-table thead th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600 !important;
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

.custom-table th:first-child {
    width: 60px; /* عرض ثابت */
    font-size: 0.75rem; /* تصغير الخط */
    text-align: center;
}
.custom-table td:first-child {
    text-align: center;
    font-size: 0.75rem;
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 106, 0, 0.1);
  color: #ff6a00;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 1.6rem;
  margin-right: 10px;
  position: relative;
  transition: transform 0.6s ease; /* لتدوير الأيقونة عند hover */
}

/* حركة التدوير عند hover */
.stat-icon:hover {
  transform: rotate(360deg);
}

/* النبض المستمر */
.stat-icon::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgba(255, 106, 0, 0.2);
  animation: pulse 1.5s infinite;
  top: 0;
  left: 0;
  z-index: -1;
}

/* تعريف النبض */
@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 0.6;
  }
  50% {
    transform: scale(1.4);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 0.6;
  }
}

/* ترتيب العنوان مع الدائرة */
.page-title {
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 1.5rem;
}

/* تصميم الدور */
.role-badge {
  background-color: #fff3e0; /* خلفية فاتحة */
  color: #ff8800; /* نص برتقالي */
  border: 1px solid #ff8800;
  border-radius: 50px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.role-badge:hover {
  background-color: #ff8800;
  color: #fff;
  box-shadow: 0 0 10px rgba(255,136,0,0.6);
}

/* النقطة البوليتية */
.role-bullet {
  display: inline-block;
  width: 10px;
  height: 10px;
  background-color: #ff8800;
  border-radius: 50%;
  margin-right: 8px;
  animation: pulse_bullet 1.5s infinite;
}

@keyframes pulse_bullet {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.4); opacity: 0.7; }
  100% { transform: scale(1); opacity: 1; }
}

/* إضافة bullet قبل النص */
.role-badge .bullet {
  width: 10px;
  height: 10px;
  background-color: #ff8800;
  border-radius: 50%;
  margin-right: 8px;
  display: inline-block;
  animation: pulse 1.5s infinite;
}

/* Pagination Styling */
.pagination .page-link {
    color: #ff6a00;
    border-color: #ff6a00;
    transition: all 0.2s ease-in-out;
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
    color: #aaa;
    border-color: #ccc;
}
</style>
<?php endif; ?>
<?php 
/*$rows = $pdo->query("
  SELECT users.*, roles.name AS role_name 
  FROM users 
  LEFT JOIN roles ON users.role_id = roles.id 
  ORDER BY users.id DESC
")->fetchAll();*/
//$can_edit = in_array(current_role(), ['admin','manager']);

$kw = trim($_GET['kw'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10; // عدد المستخدمين في كل صفحة
$offset = ($page - 1) * $per_page;

// إجمالي عدد الصفوف
$count_sql = "SELECT COUNT(*) 
              FROM users 
              LEFT JOIN roles ON users.role_id = roles.id 
              WHERE users.username LIKE ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(["%$kw%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

// جلب البيانات مع التصفية والصفحات
$sql = "SELECT users.*, roles.name AS role_name 
        FROM users 
        LEFT JOIN roles ON users.role_id = roles.id 
        WHERE users.username LIKE ?
        ORDER BY users.id DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$kw%"]);
$rows = $stmt->fetchAll();

?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title">
  <span class="stat-icon">
    <i class="bi bi-people"></i>
  </span>
   المستخدمون
 </h3>
  <?php if(has_permission('users.add')): ?>
  <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> مستخدم</button>
  <?php endif ?>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table">
    <thead class="table-light border-bottom small-header text-center text-secondary">
      <tr>
        <th>#</th>
        <th>اسم المستخدم</th>
        <th>الدور</th>
        <th>تاريخ الإنشاء</th>
        <?php if(has_permission('users.processes')): ?><th>عمليات</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php foreach($rows as $r): ?>
      <tr>
        <td data-label="#" class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td data-label="اسم المستخدم"><?= esc($r['username']) ?></td>
        <!--<td>
          <span class="badge bg-light text-dark border fw-semibold px-3 py-2">
            <i class="bi bi-person-badge me-1"></i> <?= esc($r['role_name'] ?? '-') ?>
          </span>
        </td>-->
        <td data-label="الدور">
          <span class="role-badge position-relative d-inline-flex align-items-center px-3 py-2 fw-semibold">
            <span class="bullet"></span>
            <i class="bi bi-person-badge me-2"></i> <?= esc($r['role_name'] ?? '-') ?>
          </span>
        </td>
        <td data-label="تاريخ الانشاء" class="text-secondary small"><?= esc($r['created_at']) ?></td>
        <?php if(has_permission('users.processes')): ?>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionsUser<?= $r['id'] ?>">
              <i class="bi bi-gear-fill"></i>
            </button>
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
          <!--<div>
            <label class="form-label">الدور</label>
            <select name="role" class="form-select">
              <?php foreach(['admin','manager','staff'] as $ro): ?>
                <option <?= $r['role']===$ro?'selected':'' ?>><?= $ro ?></option>
              <?php endforeach; ?>
            </select>
          </div>-->
          <div>
            <label class="form-label">الدور</label>
            <?php $roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll(); ?>
            <select name="role_id" class="form-select" required>
              <option value="">اختر الدور</option>
              <?php foreach ($roles as $ro): ?>
                <option value="<?= $ro['id'] ?>" <?= $r['role_id'] == $ro['id'] ? 'selected' : '' ?>>
                  <?= esc($ro['name']) ?>
                </option>
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


<?php if ($total_pages > 1): ?>
<nav aria-label="صفحات المستخدمين" class="mt-3">
  <ul class="pagination justify-content-center flex-wrap">
    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=1">الأول</a>
    </li>

    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $page - 1 ?>">السابق</a>
    </li>

    <?php
    $max_links = 5;
    $start = max($page - 2, 1);
    $end = min($page + 2, $total_pages);

    if ($start > 1)
      echo '<li class="page-item disabled"><span class="page-link">…</span></li>';

    for ($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor;

    if ($end < $total_pages)
      echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
    ?>

    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $page + 1 ?>">التالي</a>
    </li>

    <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
      <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $total_pages ?>">الأخير</a>
    </li>
  </ul>
</nav>
<?php endif; ?>


<?php foreach($rows as $r): ?>
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
        <?php if(has_permission('users.edit')): ?>
        <button class="btn btn-outline-warning w-100 mb-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>">
          <i class="bi bi-pencil me-2"></i> تعديل
        </button>
        <?php endif; ?>

        <?php if(has_permission('users.delete') && $r['id'] != $_SESSION['user_id']): ?>
        <button class="btn btn-outline-danger w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#d<?= $r['id'] ?>">
          <i class="bi bi-trash me-2"></i> حذف
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>


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
            <!--<select name="role" class="form-select">
              <option>admin</option>
              <option>manager</option>
              <option>staff</option>
            </select>-->
            <?php $roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll(); ?>
            <select name="role_id" class="form-select">
              <?php foreach($roles as $ro): ?>
                <option value="<?= $ro['id'] ?>"><?= esc($ro['name']) ?></option>
              <?php endforeach; ?>
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
