<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM subscriptions WHERE 1"; 
$ps = [];
if($kw!==''){ $q .= " AND service_name LIKE ?"; $ps[] = "%$kw%"; }
$q .= " ORDER BY id DESC";
$s = $pdo->prepare($q); $s->execute($ps); $rows = $s->fetchAll();
$can_edit = in_array(current_role(), ['admin','manager']);
?>
<?php require __DIR__.'/partials/header.php'; ?>

<style>
.custom-file-upload{border:2px dashed #ccc;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;padding:20px;text-align:center;transition:all .3s;background:#f9f9f9}
.custom-file-upload:hover{border-color:#0d6efd;background:#eef5ff}
.custom-file-upload i{font-size:40px;color:#0d6efd;margin-bottom:10px}
.custom-file-upload span{font-size:14px;color:#666}
.custom-file-upload img{max-height:120px;margin-top:10px;border-radius:8px}
input[type="file"]{display:none}
</style>

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

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h3 class="mb-0">الاشتراكات والخدمات</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث بالخدمة" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <a class="btn btn-outline-dark" href="export_subscriptions_excel.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_subscriptions_pdf.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <?php if($can_edit): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addSubscription"><i class="bi bi-plus-lg"></i> إضافة</button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>#</th>
<th>اسم الخدمة</th>
<th>المشتركين</th>
<th>نوع الاشتراك</th>
<th>السعر</th>
<th>الدافع</th>
<th>فاتورة الدفع</th>
<?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['service_name']) ?></td>
<td><?= esc($r['subscribers']) ?></td>
<td><?= esc($r['subscription_type']) ?></td>
<td><?= number_format((float)$r['amount'],2) ?></td>
<td><?= esc($r['payer']) ?></td>
<td><?php if($r['invoice_image']): ?><img src="uploads/<?= esc($r['invoice_image']) ?>" width="50"><?php endif; ?></td>
<?php if($can_edit): ?>
<td>
<button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
<button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
</td>
<?php endif; ?>
</tr>

<!-- مودال التعديل -->
<?php if($can_edit): ?>
<div class="modal fade" id="edit<?= $r['id'] ?>">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="subscription_edit" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">
      <div class="modal-header"><h5 class="modal-title">تعديل الاشتراك</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body vstack gap-3">
        <input name="service_name" class="form-control" value="<?= esc($r['service_name']) ?>" placeholder="اسم الخدمة">
        <input name="subscribers" class="form-control" value="<?= esc($r['subscribers']) ?>" placeholder="المشتركين">
        <select name="subscription_type" class="form-select">
          <option <?= $r['subscription_type']=='شهري'?'selected':'' ?>>شهري</option>
          <option <?= $r['subscription_type']=='سنوي'?'selected':'' ?>>سنوي</option>
        </select>
        <input type="number" step="0.01" name="amount" class="form-control" value="<?= esc($r['amount']) ?>" placeholder="السعر">
        <select name="payer" class="form-select">
          <option <?= $r['payer']=='شركة'?'selected':'' ?>>شركة</option>
          <option <?= $r['payer']=='مؤسسة'?'selected':'' ?>>مؤسسة</option>
          <option <?= $r['payer']=='فيصل المطيري'?'selected':'' ?>>فيصل المطيري</option>
          <option <?= $r['payer']=='بسام'?'selected':'' ?>>بسام</option>
        </select>
        <label class="custom-file-upload w-100">
          <i class="bi bi-image"></i>
          <span id="file-text-edit-<?= $r['id'] ?>">اختر فاتورة</span>
          <input type="file" name="invoice_image" accept="image/*" onchange="previewFile(this,'file-text-edit-<?= $r['id'] ?>','preview-edit-<?= $r['id'] ?>')">
          <?php if(!empty($r['invoice_image'])): ?>
            <img id="preview-edit-<?= $r['id'] ?>" src="uploads/<?= esc($r['invoice_image']) ?>" style="max-width:100px;margin-top:8px"/>
          <?php else: ?>
            <img id="preview-edit-<?= $r['id'] ?>" style="display:none;max-width:100px;margin-top:8px"/>
          <?php endif; ?>
        </label>
      </div>
      <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
    </form>
  </div></div>
</div>
<?php endif; ?>

<!-- مودال الحذف -->
<?php if($can_edit): ?>
<div class="modal fade" id="del<?= $r['id'] ?>">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">تأكيد الحذف</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">هل أنت متأكد أنك تريد حذف الاشتراك <b><?= esc($r['service_name']) ?></b> ؟</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
      <a href="subscription_delete?id=<?= $r['id'] ?>" class="btn btn-danger">حذف</a>
    </div>
  </div></div>
</div>
<?php endif; ?>

<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- إضافة مودال -->
<?php if($can_edit): ?>
<div class="modal fade" id="addSubscription">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="subscription_add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">إضافة اشتراك</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button> <!-- X -->
        </div>
        <div class="modal-body vstack gap-3">
          <input required name="service_name" class="form-control" placeholder="اسم الخدمة">
          <input required name="subscribers" class="form-control" placeholder="المشتركين">
          <select name="subscription_type" class="form-select">
            <option>شهري</option>
            <option>سنوي</option>
          </select>
          <input type="number" step="0.01" name="amount" class="form-control" placeholder="السعر">
          <select name="payer" class="form-select">
            <option>شركة</option>
            <option>مؤسسة</option>
            <option>فيصل المطيري</option>
            <option>بسام</option>
          </select>
          <label class="custom-file-upload w-100">
            <i class="bi bi-image"></i>
            <span id="file-text-add">اختر فاتورة</span>
            <input required type="file" name="invoice_image" accept="image/*" onchange="previewFile(this,'file-text-add','preview-add')">
            <img id="preview-add" style="display:none;max-width:100px;margin-top:8px"/>
          </label>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-orange">حفظ</button> <!-- submit صريح -->
        </div>
      </form>
    </div>
  </div>
</div>

<?php endif; ?>

<script>
function previewFile(input,textId,previewId){
const file=input.files[0];
if(file){document.getElementById(textId).textContent=file.name;
const reader=new FileReader();
reader.onload=function(e){document.getElementById(previewId).src=e.target.result;document.getElementById(previewId).style.display="block";}
reader.readAsDataURL(file);}
}
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
