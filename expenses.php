<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM expenses WHERE 1"; 
$ps = [];
if($kw!==''){ $q .= " AND main_expense LIKE ?"; $ps[] = "%$kw%"; }
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
  <h3 class="mb-0">المصروفات</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث بالخانة الأولى" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <?php if($can_edit): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addExpense"><i class="bi bi-plus-lg"></i> إضافة</button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>#</th>
<th>الخانة الأولى</th>
<th>الخانة الثانية</th>
<th>بيان المصروف</th>
<th>قيمة المصروف</th>
<th>المرفق</th>
<?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= esc($r['main_expense']) ?></td>
<td><?= esc($r['sub_expense']) ?></td>
<td><?= esc($r['expense_desc']) ?></td>
<td><?= number_format((float)$r['expense_amount'],2) ?></td>
<td><?php if($r['expense_file']): ?><img src="uploads/<?= esc($r['expense_file']) ?>" width="50"><?php endif; ?></td>
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
    <form method="post" action="expenses_edit.php" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">
      <div class="modal-header"><h5 class="modal-title">تعديل مصروف</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body vstack gap-3">
        <!-- الخانة الأولى -->
        <select id="main_expense_edit<?= $r['id'] ?>" name="main_expense" class="form-select" required>
          <option value="">اختر</option>
          <option <?= $r['main_expense']=="ايجارات"?"selected":"" ?>>ايجارات</option>
          <option <?= $r['main_expense']=="حكومية"?"selected":"" ?>>حكومية</option>
          <option <?= $r['main_expense']=="مرافق وخدمات"?"selected":"" ?>>مرافق وخدمات</option>
          <option <?= $r['main_expense']=="رواتب"?"selected":"" ?>>رواتب</option>
          <option <?= $r['main_expense']=="سكن"?"selected":"" ?>>سكن</option>
          <option <?= $r['main_expense']=="مصروفات اخرى"?"selected":"" ?>>مصروفات اخرى</option>
        </select>

        <!-- الخانة الثانية -->
        <select id="sub_expense_edit<?= $r['id'] ?>" name="sub_expense" class="form-select" required>
          <option value="<?= esc($r['sub_expense']) ?>"><?= esc($r['sub_expense']) ?></option>
        </select>

        <!-- البيان -->
        <input name="expense_desc" class="form-control" value="<?= esc($r['expense_desc']) ?>" placeholder="شرح المصروف">

        <!-- القيمة -->
        <input type="number" step="0.01" name="expense_amount" class="form-control" value="<?= esc($r['expense_amount']) ?>">

        <!-- المرفق -->
        <label class="custom-file-upload w-100">
          <i class="bi bi-image"></i>
          <span id="file-text-edit-<?= $r['id'] ?>">اختر مرفق</span>
          <input type="file" name="expense_file" accept="image/*" onchange="previewFile(this,'file-text-edit-<?= $r['id'] ?>','preview-edit-<?= $r['id'] ?>')">
          <?php if(!empty($r['expense_file'])): ?>
            <img id="preview-edit-<?= $r['id'] ?>" src="uploads/<?= esc($r['expense_file']) ?>" style="max-width:100px;margin-top:8px"/>
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
    <div class="modal-body">هل أنت متأكد أنك تريد حذف المصروف <b><?= esc($r['expense_desc']) ?></b> ؟</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
      <a href="expenses_delete.php?id=<?= $r['id'] ?>" class="btn btn-danger">حذف</a>
    </div>
  </div></div>
</div>
<?php endif; ?>

<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- مودال الإضافة -->
<?php if($can_edit): ?>
<div class="modal fade" id="addExpense">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="expenses_add.php" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header"><h5 class="modal-title">إضافة مصروف</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body vstack gap-3">

          <label>الخانة الأولى</label>
          <select id="main_expense" name="main_expense" class="form-select" required>
            <option value="">اختر</option>
            <option value="ايجارات">ايجارات</option>
            <option value="حكومية">حكومية</option>
            <option value="مرافق وخدمات">مرافق وخدمات</option>
            <option value="رواتب">رواتب</option>
            <option value="سكن">سكن</option>
            <option value="مصروفات اخرى">مصروفات اخرى</option>
          </select>

          <label>الخانة الثانية</label>
          <select id="sub_expense" name="sub_expense" class="form-select" required>
            <option value="">اختر من الخانة الأولى</option>
          </select>

          <label>الخانة الثالثة</label>
          <input type="text" name="expense_desc" class="form-control" placeholder="ادخال شرح المصروف">

          <label>الخانة الرابعة</label>
          <input type="number" step="0.01" name="expense_amount" class="form-control" placeholder="المبلغ" required>

          <label>الخانة الخامسة</label>
          <label class="custom-file-upload w-100">
            <i class="bi bi-image"></i>
            <span id="file-text-add">اختر مرفق</span>
            <input type="file" name="expense_file" accept="image/*" onchange="previewFile(this,'file-text-add','preview-add')">
            <img id="preview-add" style="display:none;max-width:100px;margin-top:8px"/>
          </label>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-orange">حفظ</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const expenseTypes = {
  "حكومية": ["إقامات ونقل كفالة","تأمينات","أخرى"],
  "مرافق وخدمات": ["كهرباء","مياه","غاز","هاتف وانترنت","أخرى"],
  "رواتب": ["رواتب موظفين","أخرى"],
  "سكن": ["سكن وإعاشة","كهرباء","مياه","أخرى"],
  "ايجارات": ["أخرى"],
  "مصروفات اخرى": ["أخرى"]
};

function fillSub(mainId,subId){
  let main = document.getElementById(mainId);
  let sub = document.getElementById(subId);
  if(!main || !sub) return;
  sub.innerHTML = "<option value=''>اختر</option>";
  if(expenseTypes[main.value]){
    expenseTypes[main.value].forEach(v=>{
      let opt=document.createElement("option");
      opt.value=v; opt.textContent=v;
      sub.appendChild(opt);
    });
  }
}

document.getElementById("main_expense")?.addEventListener("change", ()=>fillSub("main_expense","sub_expense"));

function previewFile(input,textId,previewId){
  const file=input.files[0];
  if(file){
    document.getElementById(textId).textContent=file.name;
    const reader=new FileReader();
    reader.onload=function(e){
      document.getElementById(previewId).src=e.target.result;
      document.getElementById(previewId).style.display="block";
    }
    reader.readAsDataURL(file);
  }
}
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
