<?php require __DIR__.'/partials/header.php'; ?>
<style>
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

</style>
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

$perPage = 10; // عدد الصفوف في الصفحة
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// بناء استعلام العد الكلي
$count_query = "SELECT COUNT(*) as total FROM custodies WHERE 1";
$count_params = [];

if($kw!==''){ 
  $count_query .= " AND person_name LIKE ?"; 
  $count_params[] = "%$kw%"; 
}

$stmtTotal = $pdo->prepare($count_query);
$stmtTotal->execute($count_params);
$total_rows = $stmtTotal->fetch()['total'];
$total_pages = ceil($total_rows / $perPage);

// حساب الـ offset
$offset = ($page - 1) * $perPage;

// بناء الاستعلام الرئيسي مع LIMIT
$q = "SELECT * FROM custodies WHERE 1";
$ps = [];
if($kw!==''){ 
  $q.=" AND person_name LIKE ?"; 
  $ps[]="%$kw%"; 
}
$q.=" ORDER BY taken_at ASC LIMIT $perPage OFFSET $offset";

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

$w = "SELECT * FROM custodies WHERE 1";
$pz = [];
if($kw!==''){ 
  $w.=" AND person_name LIKE ?"; 
  $pz[]="%$kw%"; 
}

$d=$pdo->prepare($w);
$d->execute($pz);
$rowsa=$d->fetchAll();

foreach($rowsa as $f) {
  $total_in += (float)$f['main_amount'];
  $total_out += ((float)$f['main_amount'] - (float)$f['amount']);
}
$total_balance = $total_in - $total_out;
?>

<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
    <!-- صف الإجماليات -->
    <tr class="fw-bold text-center">
      <th colspan="2" style="background:#f0f0f0;">الرصيد</th>
      <th style="background:#d4edda;">الصادر</th>
      <th style="background:#fff3cd;">الوارد</th>
    </tr>
    <tr class="fw-bold text-center">
      <th colspan="2" style="background:#e9ecef;"><?= number_format($total_balance, 2) ?></th>
      <th style="background:#d4edda;"><?= number_format($total_out, 2) ?></th>
      <th style="background:#fff3cd;"><?= number_format($total_in, 2) ?></th>
    </tr>

    <!-- عناوين الأعمدة -->
    <tr class="table-light">
      <th>#</th>
      <th>البيان</th>
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
        $in = (float)$r['main_amount'];  // الوارد
        $remain = (float)$r['sub_amount'];   // المتبقي
        $out = $in - $remain;            // المصروف
        if($out < 0) $out = 0;

        // جلب الحركات المرتبطة بالعهدة
        $transactions_stmt->execute([$r['id']]);
        $transactions = $transactions_stmt->fetchAll();

        if(count($transactions) > 0){
            // لو فيه حركة، الرصيد يبدأ من الوارد - الصادر
            $current_balance = $in - $out;
        } else {
            // لو مفيش حركة، الرصيد يعتمد على آخر رصيد محسوب
            $current_balance = $last_balance + $in - $out;
        }

        // تحديث الرصيد الأخير للصفوف التالية
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
        <!--<td>
          <a class="btn btn-sm btn-outline-primary" href="invoice_custody?id=<?= $r['id'] ?>"><i class="bi bi-printer"></i></a>
          <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
        </td>-->
        <td>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actions<?= $r['id'] ?>">
            <i class="bi bi-gear"></i>
          </button>

          <!-- مودال العمليات -->
          <div class="modal fade" id="actions<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">العمليات على العهدة رقم <?= $r['id'] ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                  <div class="d-grid gap-2">
                    <a href="invoice_custody?id=<?= $r['id'] ?>" class="btn btn-outline-primary">
                      <i class="bi bi-printer me-1"></i> طباعة
                    </a>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>" data-bs-dismiss="modal">
                      <i class="bi bi-pencil me-1"></i> تعديل
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $r['id'] ?>" data-bs-dismiss="modal">
                      <i class="bi bi-trash me-1"></i> حذف
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
    </tr>

    <?php 
    // استعراض الحركات
    foreach($transactions as $t):
        $trans_amount = (float)$t['amount'];

        // خصم الحركة من الرصيد الحالي
        $current_balance -= $trans_amount;

        // تحديث آخر رصيد بعد كل حركة
        $last_balance = $current_balance;

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
        <td><?= esc($r['person_name']) ?> -- <?= $type_ar ?></td>
        <td></td>
        <td><?= number_format($trans_amount,2) ?></td>
        <td><?= number_format($current_balance,2) ?></td>
        <td><?= esc($t['created_at']) ?></td>
        <td><?= esc($t['notes'] ?? '') ?></td>
        <td><?= $type_ar ?></td>
        <?php if($can_edit): ?><td></td><?php endif; ?>
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
<?php if ($total_pages > 1): ?>
<nav aria-label="صفحات النتائج" class="mt-3">
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

    if($start > 1){
        echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }

    for($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor;

    if($end < $total_pages){
        echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
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
