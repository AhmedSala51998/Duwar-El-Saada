<?php 
require __DIR__.'/partials/header.php'; 
require_permission('systems_settings.view'); // صلاحية عرض الإعدادات

// Toast للرسائل
if(!empty($_SESSION['toast'])): 
    $toast=$_SESSION['toast']; 
    unset($_SESSION['toast']); 
?>
<div class="position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="liveToast" class="toast align-items-center text-bg-<?= $toast['type'] ?> border-0 show fade">
    <div class="d-flex">
      <div class="toast-body"><?= esc($toast['msg']) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded",()=>{let el=document.getElementById("liveToast");if(el){new bootstrap.Toast(el,{delay:2500}).show();}});
</script>
<?php endif; ?>

<style>
/* --- إعادة استخدام تصميم جدول المستخدمين --- */
.custom-table {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
}
.custom-table thead th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600 !important;
  border-bottom: 2px solid #dee2e6;
  vertical-align: middle;
  font-size: 0.85rem;
  white-space: nowrap;
}
.custom-table tbody tr:hover {
  background-color: #f1f5ff;
  box-shadow: inset 0 0 0 9999px rgba(0,0,0,0.02);
}
.custom-table td, .custom-table th { padding: 0.6rem 0.75rem; vertical-align: middle; }
.custom-table td img { max-width: 80px; border-radius:5px; }
.btn-orange { background-color: #ff6a00; color: #fff; border:none; transition: all 0.3s ease;}
.btn-orange:hover { background-color: #e85d00; }
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
</style>

<?php
// Pagination & البحث
$kw = trim($_GET['kw'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// إجمالي عدد الصفوف
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE main_logo LIKE ? OR secondary_logo LIKE ? OR text1 LIKE ? OR text2 LIKE ? OR footer_text LIKE ?");
$count_stmt->execute(["%$kw%","%$kw%","%$kw%","%$kw%","%$kw%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

// البيانات
$sql = "SELECT * FROM system_settings 
        WHERE main_logo LIKE ? OR secondary_logo LIKE ? OR text1 LIKE ? OR text2 LIKE ? OR footer_text LIKE ?
        ORDER BY id DESC
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$kw%","%$kw%","%$kw%","%$kw%","%$kw%"]);
$rows = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h3 class="page-title mb-0 d-flex align-items-center gap-2">
    <span class="stat-icon"><i class="bi bi-gear-fill"></i></span> إعدادات النظام
  </h3>

  <div class="d-flex align-items-center gap-2 flex-wrap">
    <!-- بحث -->
    <form method="get" class="d-flex align-items-center gap-2 search-form">
      <div class="input-group" style="max-width:250px;">
        <span class="input-group-text bg-white border-orange text-orange"><i class="bi bi-search"></i></span>
        <input type="text" name="kw" class="form-control border-orange" placeholder="بحث..." value="<?= esc($kw) ?>">
      </div>
      <button type="submit" class="btn btn-orange"><i class="bi bi-search"></i></button>
      <?php if($kw!==''): ?><a href="settings.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a><?php endif; ?>
    </form>

    <!-- زر إضافة -->
    <?php if(has_permission('systems_settings.add')): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addSetting">
        <i class="bi bi-plus-lg"></i> إضافة
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
<table class="table table-hover align-middle mb-0 custom-table text-center">
  <thead class="table-light border-bottom small-header text-secondary">
    <tr>
      <th>#</th>
      <th>الشعار الرئيسي</th>
      <th>الشعار الثانوي</th>
      <th>النص 1</th>
      <th>النص 2</th>
      <th>نص الفوتر</th>
      <?php if(has_permission('systems_settings.edit') || has_permission('systems_settings.delete')): ?><th>عمليات</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= $r['main_logo'] ? '<img src="'.esc($r['main_logo']).'" />' : '-' ?></td>
        <td><?= $r['secondary_logo'] ? '<img src="'.esc($r['secondary_logo']).'" />' : '-' ?></td>
        <td><?= esc($r['text1']) ?></td>
        <td><?= esc($r['text2']) ?></td>
        <td><?= esc($r['footer_text']) ?></td>
        <?php if(has_permission('systems_settings.edit') || has_permission('systems_settings.delete')): ?>
          <td>
            <?php if(has_permission('systems_settings.edit')): ?>
              <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>">
                <i class="bi bi-pencil"></i>
              </button>
            <?php endif; ?>
            <?php if(has_permission('systems_settings.delete')): ?>
              <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $r['id'] ?>">
                <i class="bi bi-trash"></i>
              </button>
            <?php endif; ?>
          </td>
        <?php endif; ?>
      </tr>

      <!-- مودال تعديل -->
      <div class="modal fade" id="edit<?= $r['id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="setting_edit" enctype="multipart/form-data">
              <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-header">
                <h5 class="modal-title">تعديل الإعداد</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body vstack gap-3">

                <!-- الشعار الرئيسي -->
                <div>
                  <label class="form-label">الشعار الرئيسي</label>
                  <label class="custom-file-upload w-100">
                    <i class="bi bi-image"></i>
                    <span id="file-text-main<?= $r['id'] ?>"><?= $r['main_logo'] ? basename($r['main_logo']) : 'اختر صورة' ?></span>
                    <input type="file" name="main_logo" accept="image/*" 
                          onchange="previewFileCustom(this,'file-text-main<?= $r['id'] ?>','preview-main<?= $r['id'] ?>')">
                    <img id="preview-main<?= $r['id'] ?>" src="<?= esc($r['main_logo']) ?>" style="display:<?= $r['main_logo'] ? 'block' : 'none' ?>;max-width:100px;margin-top:8px;">
                  </label>
                </div>

                <!-- الشعار الثانوي -->
                <div>
                  <label class="form-label">الشعار الثانوي</label>
                  <label class="custom-file-upload w-100">
                    <i class="bi bi-image"></i>
                    <span id="file-text-secondary<?= $r['id'] ?>"><?= $r['secondary_logo'] ? basename($r['secondary_logo']) : 'اختر صورة' ?></span>
                    <input type="file" name="secondary_logo" accept="image/*" 
                          onchange="previewFileCustom(this,'file-text-secondary<?= $r['id'] ?>','preview-secondary<?= $r['id'] ?>')">
                    <img id="preview-secondary<?= $r['id'] ?>" src="<?= esc($r['secondary_logo']) ?>" style="display:<?= $r['secondary_logo'] ? 'block' : 'none' ?>;max-width:100px;margin-top:8px;">
                  </label>
                </div>

                <div><label>النص 1</label><input name="text1" class="form-control" value="<?= esc($r['text1']) ?>"></div>
                <div><label>النص 2</label><textarea name="text2" class="form-control"><?= esc($r['text2']) ?></textarea></div>
                <div><label>نص الفوتر</label><textarea name="footer_text" class="form-control"><?= esc($r['footer_text']) ?></textarea></div>

              </div>
              <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
            </form>
          </div>
        </div>
      </div>

      <!-- مودال حذف -->
      <div class="modal fade" id="delete<?= $r['id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="setting_delete">
              <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-header">
                <h5 class="modal-title">تأكيد الحذف</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                هل أنت متأكد من حذف هذا الإعداد؟
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
</div>

<!-- مودال إضافة -->
<?php if(has_permission('systems_settings.add')): ?>
<div class="modal fade" id="addSetting">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="setting_add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">إضافة إعداد جديد</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body vstack gap-3">

          <!-- الشعار الرئيسي -->
          <div>
            <label class="form-label">الشعار الرئيسي</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-image"></i>
              <span id="file-text-mainAdd">اختر صورة</span>
              <input type="file" name="main_logo" accept="image/*" 
                     onchange="previewFileCustom(this,'file-text-mainAdd','preview-mainAdd')">
              <img id="preview-mainAdd" style="display:none;max-width:100px;margin-top:8px;">
            </label>
          </div>

          <!-- الشعار الثانوي -->
          <div>
            <label class="form-label">الشعار الثانوي</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-image"></i>
              <span id="file-text-secondaryAdd">اختر صورة</span>
              <input type="file" name="secondary_logo" accept="image/*" 
                     onchange="previewFileCustom(this,'file-text-secondaryAdd','preview-secondaryAdd')">
              <img id="preview-secondaryAdd" style="display:none;max-width:100px;margin-top:8px;">
            </label>
          </div>

          <div><label>النص 1</label><input name="text1" class="form-control"></div>
          <div><label>النص 2</label><textarea name="text2" class="form-control"></textarea></div>
          <div><label>نص الفوتر</label><textarea name="footer_text" class="form-control"></textarea></div>

        </div>
        <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
// معاينة الصور مع تغيير النص
function previewFileCustom(input, textId, previewId){
  const file = input.files[0];
  const preview = document.getElementById(previewId);
  const textEl = document.getElementById(textId);
  if(file){
    textEl.textContent = file.name;
    preview.style.display = 'block';
    preview.src = URL.createObjectURL(file);
  }
}
</script>
