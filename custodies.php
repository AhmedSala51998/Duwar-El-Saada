<?php require __DIR__.'/partials/header.php'; require_permission('custodies.view'); ?>
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


.custom-table {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem; /* تصغير النص قليلاً للراحة البصرية */
}

.custom-table thead th {
  background: #f8f9fa;
  color: #495057;
  font-weight: 600;
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


.custom-table th,
.custom-table td {
    text-align: center;
    vertical-align: middle;
    padding: 0.5rem 0.6rem;
}

/* عرض الأعمدة */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) { width: 3%; } /* # */
.custom-table th:nth-child(2),
.custom-table td:nth-child(2) { width: 15%; } /* البيان */
.custom-table th:nth-child(3),
.custom-table td:nth-child(3) { width: 10%; } /* الوارد */
.custom-table th:nth-child(4),
.custom-table td:nth-child(4) { width: 10%; } /* الصادر */
.custom-table th:nth-child(5),
.custom-table td:nth-child(5) { width: 10%; } /* الرصيد */
.custom-table th:nth-child(6),
.custom-table td:nth-child(6) { width: 14%; } /* التاريخ */
.custom-table th:nth-child(7),
.custom-table td:nth-child(7) { width: 32%; } /* الملاحظات */
.custom-table th:nth-child(8),
.custom-table td:nth-child(8) { 
  width: 6%;   /* زوّدها شوية */
  min-width: 90px;  /* تأكد إن المساحة كافية للزر */
  text-align: center;
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

// بناء استعلام العد الكلي مع الانضمام للفرع
$count_query = "SELECT COUNT(*) as total 
                FROM custodies c
                LEFT JOIN branches b ON b.id = c.branch_id
                WHERE 1";
$count_params = [];

if($kw !== ''){ 
  $count_query .= " AND c.person_name LIKE ?"; 
  $count_params[] = "%$kw%"; 
}

$stmtTotal = $pdo->prepare($count_query);
$stmtTotal->execute($count_params);
$total_rows = $stmtTotal->fetch()['total'];
$total_pages = ceil($total_rows / $perPage);

// حساب الـ offset
$offset = ($page - 1) * $perPage;

// بناء الاستعلام الرئيسي مع LEFT JOIN لجلب اسم الفرع
$q = "SELECT c.*, b.branch_name
      FROM custodies c
      LEFT JOIN branches b ON b.id = c.branch_id
      WHERE 1";
$ps = [];
if($kw !== ''){ 
  $q .= " AND c.person_name LIKE ?"; 
  $ps[] = "%$kw%"; 
}
$q .= " ORDER BY c.taken_at ASC LIMIT $perPage OFFSET $offset";

$s = $pdo->prepare($q);
$s->execute($ps);
$rows = $s->fetchAll();

// جلب الحركات لكل عهدة
$transactions_stmt = $pdo->prepare("SELECT * FROM custody_transactions WHERE custody_id=? ORDER BY created_at ASC");

//$can_edit = in_array(current_role(), ['admin','manager']);
$options = ['بسام','فيصل المطيري','مؤسسة','شركة'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h3 class="page-title mb-0">
    <span class="stat-icon"><i class="bi bi-wallet2"></i></span>
    العُهد
  </h3>

  <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center page-actions">
    <form class="d-flex gap-2 flex-md-nowrap w-auto w-sm-100" method="get">
      <select name="kw" class="form-select">
        <option value="">بحث بالاسم</option>
        <?php foreach($options as $opt): ?>
          <option value="<?=esc($opt)?>" <?= $kw==$opt?'selected':'' ?>><?=esc($opt)?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline-secondary">بحث</button>
    </form>

    <?php if(has_permission('custodies.print_excel')): ?>
      <a class="btn btn-outline-dark" href="export_custodies_excel.php?kw=<?=urlencode($kw)?>">
        <i class="bi bi-file-earmark-spreadsheet"></i>
        <span class="d-none d-sm-inline">Excel</span>
      </a>
    <?php endif ?>

    <?php if(has_permission('custodies.print_pdf')): ?>
      <a class="btn btn-outline-dark" href="export_custodies_pdf.php?kw=<?=urlencode($kw)?>">
        <i class="bi bi-filetype-pdf"></i>
        <span class="d-none d-sm-inline">PDF</span>
      </a>
    <?php endif ?>

    <?php if(has_permission('custodies.add')): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add">
        <i class="bi bi-plus-lg"></i>
        <span class="d-none d-sm-inline">إضافة</span>
      </button>
    <?php endif; ?>
    <?php if(has_permission('custodies.add_group')): ?>
      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMultipleCustodies">
        <i class="bi bi-plus-circle-dotted"></i>
        <span class="d-none d-sm-inline">إضافة متعددة</span>
      </button>
    <?php endif; ?>
  </div>
</div>

<?php 
$last_balance = 0; // الرصيد السابق
$total_in = 0; 
$total_out = 0; 

$w = "SELECT c.*, b.branch_name FROM custodies c LEFT JOIN branches b ON b.id = c.branch_id WHERE 1";
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

$branches = $pdo->query("SELECT * FROM branches ORDER BY branch_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table">
    <thead class="small-header text-center text-secondary fw-semibold">
      <!-- صف الإجماليات -->
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

      <!-- عناوين الأعمدة -->
      <tr class="table-light">
        <th>#</th>
        <th>البيان</th>
        <th>الفرع</th> <!-- تم إضافة عمود الفرع -->
        <th>الوارد</th>
        <th>الصادر</th>
        <th>الرصيد</th>
        <th>التاريخ</th>
        <th>ملاحظات</th>
        <?php if(has_permission('custodies.processes')): ?><th>عمليات</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="text-center">
    <?php foreach($rows as $r): 
        $in = (float)$r['main_amount'];  
        $remain = (float)$r['sub_amount'];  
        $out = $in - $remain; if($out < 0) $out = 0;
        $transactions_stmt->execute([$r['id']]);
        $transactions = $transactions_stmt->fetchAll();
        if(count($transactions) > 0) $current_balance = $in - $out; 
        else $current_balance = $last_balance + $in - $out;
        $last_balance = $current_balance;
    ?>
      <tr class="table-primary">
        <td data-label="#" class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td data-label="البيان"><?= esc($r['person_name']) ?></td>
        <td data-label="الفرع"><?= esc($r['branch_name'] ?? '-') ?></td> <!-- عرض الفرع -->
        <td data-label="الوارد"><?= number_format($in,2) ?></td>
        <td data-label="الصادر"><?= number_format($out,2) ?></td>
        <td data-label="الرصيد"><?= number_format($current_balance,2) ?></td>
        <td data-label="التاريخ"><?= esc($r['taken_at']) ?></td>
        <td data-label="ملاحظات"><?= esc($r['notes']) ?></td>
        <?php if(has_permission('custodies.processes')): ?>
        <td class="text-center">
          <!-- عمليات مثل الطباعة والتعديل والحذف -->
        </td>
        <?php endif ?>
      </tr>

      <?php foreach($transactions as $t):
        $trans_amount = (float)$t['amount'];
        $current_balance -= $trans_amount;
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
        <td data-label="#"></td>
        <td data-label="البيان"><?= esc($r['person_name']) ?> -- <?= $type_ar ?></td>
        <td data-label="الفرع"><?= esc($r['branch_name'] ?? '-') ?></td> <!-- عرض الفرع للحركات -->
        <td data-label="الوارد"></td>
        <td data-label="الصادر"><?= number_format($trans_amount,2) ?></td>
        <td data-label="الرصيد"><?= number_format($current_balance,2) ?></td>
        <td data-label="التاريخ"><?= esc($t['created_at']) ?></td>
        <td data-label="ملاحظات"><?= esc($t['notes'] ?? '') ?></td>
        <td><?= $type_ar ?></td>
        <?php if(has_permission('custodies.processes')): ?><td></td><?php endif; ?>
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
              <div>
                <label class="form-label">الفرع</label>
                <select name="branch_id" class="form-select" required>
                  <option hidden>اختر الفرع</option>
                  <?php foreach($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $r['branch_id']==$b['id'] ? 'selected' : '' ?>>
                      <?= esc($b['branch_name']) ?>
                    </option>
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
  <ul class="pagination justify-content-center flex-wrap overflow-auto" style="gap:4px;">
    <!-- أول صفحة -->
    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=1">الأول</a>
    </li>

    <!-- السابق -->
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $page - 1 ?>">السابق</a>
    </li>

    <?php
    $max_links = 5;
    $start = max($page - 2, 1);
    $end = min($page + 2, $total_pages);

    if($start > 1){
        echo '<li class="page-item disabled"><span class="page-link px-2 py-1">…</span></li>';
    }

    for($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor;

    if($end < $total_pages){
        echo '<li class="page-item disabled"><span class="page-link px-2 py-1">…</span></li>';
    }
    ?>

    <!-- التالي -->
    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $page + 1 ?>">التالي</a>
    </li>

    <!-- آخر صفحة -->
    <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
      <a class="page-link px-2 py-1" href="?kw=<?= urlencode($kw) ?>&page=<?= $total_pages ?>">الأخير</a>
    </li>
  </ul>
</nav>
<?php endif; ?>


<!-- إضافة -->
<?php if(has_permission('custodies.add')): ?>
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
          <div>
            <label class="form-label">الفرع</label>
            <select name="branch_id" class="form-select" required>
              <option hidden>اختر الفرع</option>
              <?php foreach($branches as $b): ?>
                <option value="<?= $b['id'] ?>"><?= esc($b['branch_name']) ?></option>
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

<?php if(has_permission('custodies.add_group')): ?>
<div class="modal fade" id="addMultipleCustodies">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="custodies_add_multiple" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="bi bi-plus-square-dotted me-1"></i> إضافة عُهد متعددة</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="table-responsive">
            <table class="odoo-table" id="custodiesTable">
              <thead class="table-light">
                <tr>
                  <th>اسم الشخص</th>
                  <th>الفرع</th>
                  <th>المبلغ</th>
                  <th>التاريخ</th>
                  <th>ملاحظات</th>
                  <th width="60">إجراء</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select name="custodies[0][person_name]" class="form-select select2-person" required>
                      <option value="">اختر الشخص</option>
                      <?php foreach($options as $opt): ?>
                        <option value="<?= esc($opt) ?>"><?= esc($opt) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td>
                    <select name="custodies[0][branch_id]" class="form-select" required>
                      <option hidden>اختر الفرع</option>
                      <?php foreach($branches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= esc($b['branch_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><input type="number" step="0.01" name="custodies[0][amount]" class="form-control" required></td>
                  <td><input type="date" name="custodies[0][taken_at]" class="form-control" required></td>
                  <td><input type="text" name="custodies[0][notes]" class="form-control"></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn btn-outline-primary mt-2" id="addCustodyRow">
            <i class="bi bi-plus-circle"></i> إضافة صف جديد
          </button>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-orange">حفظ جميع العهد</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  let index = 1;

  const tbody = document.querySelector("#custodiesTable tbody");
  const templateRow = tbody.rows[0].cloneNode(true); // نسخة الصف الأول

  const personOptions = templateRow.querySelector(".select2-person").innerHTML;
  const branchOptions = templateRow.querySelector("select[name$='[branch_id]']")?.innerHTML || "";

  document.getElementById("addCustodyRow").addEventListener("click", function () {
    const newRow = templateRow.cloneNode(true);

    // إعادة تهيئة select الشخص
    const selectPerson = newRow.querySelector(".select2-person");
    selectPerson.innerHTML = personOptions;
    selectPerson.name = `custodies[${index}][person_name]`;
    selectPerson.value = "";

    // إعادة تهيئة select الفرع
    const selectBranch = newRow.querySelector("select[name$='[branch_id]']");
    if (selectBranch) {
      selectBranch.innerHTML = branchOptions;
      selectBranch.name = `custodies[${index}][branch_id]`;
      selectBranch.value = "";
    }

    // إعادة تسمية باقي الحقول
    const amountInput = newRow.querySelector("input[name^='custodies'][type='number']");
    const dateInput = newRow.querySelector("input[name^='custodies'][type='date']");
    const noteInput = newRow.querySelector("input[name^='custodies'][type='text']");

    amountInput.name = `custodies[${index}][amount]`;
    dateInput.name = `custodies[${index}][taken_at]`;
    noteInput.name = `custodies[${index}][notes]`;

    // تنظيف القيم
    amountInput.value = "";
    dateInput.value = "";
    noteInput.value = "";
    if (selectBranch) selectBranch.value = "";

    tbody.appendChild(newRow);
    index++;

    // تفعيل select2 للصف الجديد
    $(selectPerson).select2({
      width: '100%',
      dropdownParent: $('#addMultipleCustodies')
    });
  });

  // حذف صف
  document.addEventListener("click", function (e) {
    if (e.target.closest(".removeRow")) {
      const rows = document.querySelectorAll("#custodiesTable tbody tr");
      if (rows.length > 1) e.target.closest("tr").remove();
    }
  });

  // تفعيل select2 للصف الأول
  $('.select2-person').select2({
    width: '100%',
    dropdownParent: $('#addMultipleCustodies')
  });
});
</script>