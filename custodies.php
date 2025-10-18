<?php require __DIR__.'/partials/header.php'; ?>

<?php
// توست الرسائل
if(!empty($_SESSION['toast'])){ 
  $toast = $_SESSION['toast']; 
  unset($_SESSION['toast']); 
  ?>
  <div class="position-fixed top-0 end-0 p-3" style="z-index:2000">
    <div class="toast align-items-center text-bg-<?= $toast['type'] ?> border-0 show fade">
      <div class="d-flex">
        <div class="toast-body"><?= esc($toast['msg']) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded",function(){
      let el=document.querySelector(".toast");
      if(el){ new bootstrap.Toast(el,{delay:2500}).show(); }
    });
  </script>
<?php } ?>

<?php
$kw = trim($_GET['kw'] ?? '');
$q = "SELECT * FROM custodies WHERE 1";
$ps=[];
if($kw!==''){ 
  $q.=" AND person_name LIKE ?"; 
  $ps[]="%$kw%"; 
}
$q.=" ORDER BY id ASC";
$s=$pdo->prepare($q);
$s->execute($ps);
$rows=$s->fetchAll();

// جلب الحركات لكل عهدة
$transactions_stmt = $pdo->prepare("SELECT * FROM custody_transactions WHERE custody_id=? ORDER BY created_at ASC");

$can_edit = in_array(current_role(), ['admin','manager']);
$options = ['بسام','فيصل المطيري','مؤسسة','شركة'];
?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h3 class="mb-0">العُهد</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <select name="kw" class="form-select">
        <option value="">بحث بالاسم</option>
        <?php foreach($options as $opt): ?>
          <option value="<?=esc($opt)?>" <?= $kw==$opt?'selected':'' ?>><?=esc($opt)?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <a class="btn btn-outline-dark" href="export_custodies_excel.php?kw=<?=urlencode($kw)?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_custodies_pdf.php?kw=<?=urlencode($kw)?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <?php if($can_edit): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> إضافة</button>
    <?php endif; ?>
  </div>
</div>

<?php 
$last_balance = 0; // الرصيد السابق
$total_in = 0; 
$total_out = 0; 

foreach($rows as $r) {
  $total_in += (float)$r['main_amount'];
  $total_out += ((float)$r['main_amount'] - (float)$r['amount']);
}
$total_balance = $total_in - $total_out;
?>

<div class="table-responsive">
<table class="table table-hover align-middle text-center">
<thead>
    <tr class="fw-bold">
      <th colspan="2" style="background:#f0f0f0;">الرصيد</th>
      <th style="background:#d4edda;">الصادر</th>
      <th style="background:#fff3cd;">الوارد</th>
    </tr>
    <tr class="fw-bold">
      <th colspan="2" style="background:#e9ecef;"><?= number_format($total_balance, 2) ?></th>
      <th style="background:#d4edda;"><?= number_format($total_out, 2) ?></th>
      <th style="background:#fff3cd;"><?= number_format($total_in, 2) ?></th>
    </tr>
    <tr class="table-light">
      <th>#</th>
      <th>اسم الشخص</th>
      <th>الوارد</th>
      <th>الصادر</th>
      <th>الرصيد</th>
      <th>التاريخ</th>
      <th>ملاحظات</th>
      <?php if($can_edit): ?><th>عمليات</th><?php endif; ?>
    </tr>
</thead>
<tbody>
<?php
foreach($rows as $r): 
    $in = (float)$r['main_amount'];  // المبلغ المستلم (الوارد)
    $remain = (float)$r['amount'];   // المتبقي
    $out = $in - $remain;            // المصروف حتى الآن

    // لو لسه متصرفش حاجة
    if ($out <= 0) $out = 0;

    // الرصيد = الرصيد السابق + الوارد - الصادر
    $current_balance = $last_balance + $in - $out;
    $last_balance = $current_balance;
?>
<tr class="table-primary">
    <td><?= $r['id'] ?></td>
    <td><?= esc($r['person_name']) ?></td>
    <td><?= number_format($in,2) ?></td>
    <td><?= number_format($out,2) ?></td>
    <td><?= number_format($current_balance,2) ?></td>
    <td><?= esc($r['taken_at']) ?></td>
    <td><?= esc($r['notes']) ?></td>
    <?php if($can_edit): ?>
    <td>
      <a class="btn btn-sm btn-outline-primary" href="invoice_custody?id=<?= $r['id'] ?>"><i class="bi bi-printer"></i></a>
      <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
      <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
    </td>
    <?php endif; ?>
</tr>

<?php 
// الحركات المرتبطة بالعهدة
$transactions_stmt->execute([$r['id']]);
$transactions = $transactions_stmt->fetchAll();
foreach($transactions as $t):
    $trans_amount = (float)$t['amount'];

    // خصم الصادر من الرصيد
    $current_balance -= $trans_amount;

    // تحويل النوع للعربي
    $type_ar = '';
    switch($t['type']) {
        case 'asset': $type_ar = 'أصول'; break;
        case 'expense': $type_ar = 'مصروفات'; break;
        case 'purchase': $type_ar = 'مشتريات'; break;
        default: $type_ar = esc($t['type']); 
    }
?>
<tr>
    <td></td>
    <td>-- <?= $type_ar ?></td>
    <td></td>
    <td><?= number_format($trans_amount,2) ?></td>
    <td><?= number_format($current_balance,2) ?></td>
    <td><?= esc($t['created_at']) ?></td>
    <td><?= esc($t['notes'] ?? '') ?></td>
    <?php if($can_edit): ?><td>حركة</td><?php endif; ?>
</tr>

<?php endforeach; ?>

<!-- تعديل -->
<div class="modal fade" id="e<?= $r['id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="custody_edit">
        <input type="hidden" name="_csrf" value="<?=esc(csrf_token())?>">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <div class="modal-header"><h5 class="modal-title">تعديل عهدة</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">اسم الشخص</label>
            <select name="person_name" class="form-select" required>
              <?php foreach($options as $opt): ?>
                <option value="<?=esc($opt)?>" <?= $r['person_name']==$opt?'selected':'' ?>><?=esc($opt)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label class="form-label">المبلغ</label><input type="number" step="0.01" name="amount" class="form-control" value="<?=esc($r['amount'])?>" required></div>
          <div><label class="form-label">التاريخ</label><input type="date" name="taken_at" class="form-control" value="<?=esc($r['taken_at'])?>" required></div>
          <div><label class="form-label">ملاحظات</label><textarea name="notes" class="form-control"><?=esc($r['notes'])?></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
      </form>
    </div>
  </div>
</div>

<!-- حذف -->
<div class="modal fade" id="del<?= $r['id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">تأكيد الحذف</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">هل تريد حذف العهدة الخاصة بـ <b><?=esc($r['person_name'])?></b>؟</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <a href="custody_delete?id=<?=$r['id']?>" class="btn btn-danger">حذف</a>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- إضافة -->
<?php if($can_edit): ?>
<div class="modal fade" id="add">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="custody_add">
        <input type="hidden" name="_csrf" value="<?=esc(csrf_token())?>">
        <div class="modal-header"><h5 class="modal-title">إضافة عهدة</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body vstack gap-3">
          <div>
            <label class="form-label">اسم الشخص</label>
            <select name="person_name" class="form-select" required>
              <?php foreach($options as $opt): ?>
                <option value="<?=esc($opt)?>"><?=esc($opt)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label class="form-label">المبلغ</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
          <div><label class="form-label">التاريخ</label><input type="date" name="taken_at" class="form-control" required></div>
          <div><label class="form-label">ملاحظات</label><textarea name="notes" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>
