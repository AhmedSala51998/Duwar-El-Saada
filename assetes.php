<!-- CSS للستايل -->
<style>
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
<?php require __DIR__.'/partials/header.php'; ?>
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
$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM assets WHERE 1"; 
$ps=[]; 
if($kw!==''){ 
  $q.=" AND name LIKE ?"; 
  $ps[]="%$kw%"; 
} 
$q.=" ORDER BY id DESC";
$s=$pdo->prepare($q); 
$s->execute($ps); 
$rows=$s->fetchAll();
$can_edit = in_array(current_role(), ['admin','manager']);
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h3 class="mb-0">العُهد</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث بالاسم" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <a class="btn btn-outline-dark" href="export_assets_excel.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_assets_pdf.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <?php if($can_edit): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> إضافة</button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>صورة</th>
      <th>الاسم</th>
      <th>النوع</th>
      <th>العدد</th>
      <th>السعر</th>
      <th>الدافع</th>
      <th>التاريخ</th>
      <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?php if($r['image']): ?><img src="uploads/<?= esc($r['image']) ?>" width="44" class="rounded"><?php endif; ?></td>
      <td><?= esc($r['name']) ?></td>
      <td><?= esc($r['type']) ?></td>
      <td><?= (int)$r['quantity'] ?></td>
      <td><?= number_format((float)$r['price'],2) ?></td>
      <td><?= esc($r['payer_name']) ?></td>
      <td><?= esc($r['created_at']) ?></td>
      <?php if($can_edit): ?>
      <td class="table-actions">
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>">
          <i class="bi bi-trash"></i>
        </button>
      </td>
      <?php endif; ?>
    </tr>

    <!-- Modal تعديل -->
    <?php if($can_edit): ?>
    <div class="modal fade" id="e<?= $r['id'] ?>">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" action="asset_edit" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">

            <div class="modal-header">
              <h5 class="modal-title">تعديل أصل</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body vstack gap-3">
              <div>
                <label class="form-label">الاسم</label>
                <input name="name" class="form-control" value="<?= esc($r['name']) ?>" required>
              </div>
              <div>
                <label class="form-label">النوع</label>
                <input name="type" class="form-control" value="<?= esc($r['type']) ?>">
              </div>
              <div>
                <label class="form-label">العدد</label>
                <input type="number" name="quantity" class="form-control" value="<?= (int)$r['quantity'] ?>" min="1">
              </div>
              <div>
                <label class="form-label">السعر</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= esc($r['price']) ?>">
              </div>
              <div>
                <label class="form-label">اسم الدافع</label>
                <select name="payer_name" class="form-control">
                  <option value="شركة" <?= $r['payer_name']=='شركة'?'selected':'' ?>>شركة</option>
                  <option value="مؤسسة" <?= $r['payer_name']=='مؤسسة'?'selected':'' ?>>مؤسسة</option>
                  <option value="فيصل المطيري" <?= $r['payer_name']=='فيصل المطيري'?'selected':'' ?>>فيصل المطيري</option>
                  <option value="بسام" <?= $r['payer_name']=='بسام'?'selected':'' ?>>بسام</option>
                </select>
              </div>
              <div>
                <label class="form-label">صورة</label>
                <label class="custom-file-upload w-100">
                  <i class="bi bi-image"></i>
                  <span id="file-text-edit-<?= $r['id'] ?>">اختر صورة</span>
                  <input type="file" name="image" id="asset_image_edit_<?= $r['id'] ?>" accept="image/*"
                        onchange="previewFile(this,'file-text-edit-<?= $r['id'] ?>','preview-edit-<?= $r['id'] ?>')">
                  <?php if(!empty($r['image'])): ?>
                    <img id="preview-edit-<?= $r['id'] ?>" src="<?= 'uploads/'.esc($r['image']) ?>" style="max-width:100px;margin-top:8px;"/>
                  <?php else: ?>
                    <img id="preview-edit-<?= $r['id'] ?>" style="display:none;max-width:100px;margin-top:8px;"/>
                  <?php endif; ?>
                </label>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-orange">حفظ</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Modal الحذف -->
    <?php if($can_edit): ?>
    <div class="modal fade" id="del<?= $r['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">تأكيد الحذف</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            هل أنت متأكد أنك تريد حذف الأصل <b><?= esc($r['name']) ?></b> ؟
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <a href="asset_delete?id=<?= $r['id'] ?>" class="btn btn-danger">حذف</a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if($can_edit): ?>
<div class="modal fade" id="add">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="asset_add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">إضافة أصل</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">الاسم</label>
            <input name="name" class="form-control" required>
          </div>
          <div>
            <label class="form-label">النوع</label>
            <input name="type" class="form-control">
          </div>
          <div>
            <label class="form-label">العدد</label>
            <input type="number" name="quantity" class="form-control" value="1" min="1">
          </div>
          <div>
            <label class="form-label">السعر</label>
            <input type="number" step="0.01" name="price" class="form-control">
          </div>
          <div>
            <label class="form-label">اسم الدافع</label>
             <select name="payer_name" class="form-control">
                <option value="شركة">شركة</option>
                <option value="مؤسسة">مؤسسة</option>
                <option value="فيصل المطيري">فيصل المطيري</option>
                <option value="بسام">بسام</option>
              </select>
          </div>
          <div>
            <label class="form-label">صورة</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-image"></i>
              <span id="file-text-asset">اختر صورة</span>
              <input type="file" name="image" id="asset_image" accept="image/*"
                     onchange="previewFile(this,'file-text-asset','preview-asset')">
              <img id="preview-asset" style="display:none;max-width:100px;margin-top:8px;"/>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__.'/partials/footer.php'; ?>
<script>
  function previewFile(input, textId, previewId) {
    const file = input.files[0];
    if (file) {
      document.getElementById(textId).textContent = file.name;
      const reader = new FileReader();
      reader.onload = function(e) {
        const preview = document.getElementById(previewId);
        preview.src = e.target.result;
        preview.style.display = "block";
      };
      reader.readAsDataURL(file);
    }
  }
</script>
