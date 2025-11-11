<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/partials/header.php';
require_permission('expenses.view');

// جلب العهد
$custodies = $pdo->query("SELECT person_name, SUM(amount) as balance FROM custodies GROUP BY person_name")->fetchAll(PDO::FETCH_KEY_PAIR);

$kw = trim($_GET['kw'] ?? '');
$perPage = 10; // عدد الصفوف لكل صفحة
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$q = "SELECT * FROM expenses WHERE 1";
$params = [];
if($kw!==''){ 
    $q .= " AND main_expense LIKE ?"; 
    $params[] = "%$kw%"; 
}

// جلب العدد الكلي للصفوف
$stmtTotal = $pdo->prepare(str_replace("SELECT *","SELECT COUNT(*) as total",$q));
$stmtTotal->execute($params);
$total_rows = $stmtTotal->fetch()['total'];
$total_pages = ceil($total_rows / $perPage);

// حساب offset
$offset = ($page - 1) * $perPage;

// تعديل الاستعلام الأصلي ليشمل LIMIT
$q .= " ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($q); 
$stmt->execute($params); 
$rows = $stmt->fetchAll();

//$can_edit = in_array(current_role(), ['admin','manager']);

// تحضير JS لحقول التعديل
$editRowsJs = [];
foreach($rows as $r){
  $editRowsJs[] = [
    'id'=>$r['id'],
    'main'=>$r['main_expense'],
    'sub'=>$r['sub_expense'],
    'payment_source'=>$r['payment_source'] ?? '',
    'payer_name'=>$r['payer_name'] ?? ''
  ];
}
?>

<style>
.custom-file-upload{border:2px dashed #ccc;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;padding:20px;text-align:center;transition:all .3s;background:#f9f9f9}
.custom-file-upload:hover{border-color:#0d6efd;background:#eef5ff}
.custom-file-upload i{font-size:40px;color:#0d6efd;margin-bottom:10px}
.custom-file-upload span{font-size:14px;color:#666}
.custom-file-upload img{max-height:120px;margin-top:10px;border-radius:8px}
input[type="file"]{display:none}
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

/* --- تحسين مظهر الجدول --- */
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

.custom-table th:nth-child(9),
.custom-table td:nth-child(9) {
    width: 80px; /* عرض ثابت */
    font-size: 0.75rem; /* تصغير الخط */
    text-align: center;
}

.custom-table th:nth-child(9),
.custom-table td:nth-child(9) {
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
}

@media screen and (max-width: 768px) {
  .expenses-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .expenses-header .header-actions {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
  }

  .expenses-header .search-form {
    flex-direction: column;
  }

  .expenses-header .search-form input,
  .expenses-header .search-form button {
    width: 100%;
  }

  .expenses-header button {
    width: 100%;
  }
}

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

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3 expenses-header">
  <h3 class="page-title">
    <span class="stat-icon">
      <i class="bi bi-cash-stack"></i>
    </span>
    المصروفات
  </h3>

  <div class="d-flex gap-2 flex-wrap header-actions">
    <form class="d-flex gap-2 search-form" method="get">
      <input class="form-control" name="kw" placeholder="بحث بالمصروفات" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>

    <?php if(has_permission('expenses.add')): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addExpense">
        <i class="bi bi-plus-lg"></i> إضافة
      </button>
    <?php endif; ?>

    <?php if(has_permission('expenses.add_group')): ?>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMultipleExpenses">
        <i class="bi bi-list-check"></i> إضافة متعددة
      </button>
    <?php endif; ?>

    <?php if(has_permission('expenses.addExpenseExcel')): ?>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importExpensesModal">
        <i class="bi bi-file-earmark-excel"></i> استيراد Excel
      </button>
    <?php endif; ?>
    <?php if(has_permission('expenses.downloadExcelExpenses')): ?>
      <!-- 🔹 زر تحميل نموذج المصروفات -->
      <a href="uploads/sample_expenses.xlsx" download class="btn btn-outline-info d-flex align-items-center">
        <i class="bi bi-download me-1"></i> تحميل نموذج Excel
      </a>
    <?php endif; ?>
  </div>
</div>


<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table">
    <thead class="table-light border-bottom border-2 small-header">
      <tr class="text-center text-secondary fw-semibold">
        <th>#</th>
        <th>الرقم التسلسلي</th>
        <th>المصروفات</th>
        <th>نوع المصروف</th>
        <th>بيان المصروف</th>
        <th>قيمة المصروف</th>
        <th>الضريبة (15%)</th>
        <th>الإجمالي بعد الضريبة</th>
        <th>الدافع</th>
        <th>مصدر الدفع</th>
        <?php if(has_permission('expenses.processes')): ?><th>عمليات</th><?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php foreach($rows as $r): ?>
      <tr class="text-center">
        <td data-label="#" class="fw-bold text-muted"><?= $r['id'] ?></td>
        <td data-label="رقم تسلسلي"><?= esc($r['invoice_serial']) ?></td>
        <td data-label="المصروفات"><span class="badge bg-light text-dark px-3 py-2"><?= esc($r['main_expense']) ?></span></td>
        <td data-label="نوع المصروف" class="text-truncate" title="<?= esc($r['sub_expense']) ?>">
          <?= esc($r['sub_expense']) ?>
        </td>
        <td data-label="بيان المصروف" class="text-truncate" title="<?= esc($r['expense_desc']) ?>">
          <?= esc($r['expense_desc']) ?>
        </td>
        <td data-label="قيمة المصروف" class="text-success fw-semibold"><?= number_format((float)$r['expense_amount'], 2) ?></td>

        <td data-label="الضريبه">
          <?php if (!empty($r['has_vat']) && $r['has_vat'] == 1): ?>
            <span class="text-primary fw-semibold"><?= number_format((float)$r['vat_value'], 2) ?></span>
          <?php else: ?>
            <span class="text-muted small">بدون</span>
          <?php endif; ?>
        </td>

        <td data-label="الاجمالي بعد الضريبة" class="fw-bold text-dark">
          <?php if (!empty($r['has_vat']) && $r['has_vat'] == 1): ?>
            <?= number_format((float)$r['total_amount'], 2) ?>
          <?php else: ?>
            <?= number_format((float)$r['expense_amount'], 2) ?>
          <?php endif; ?>
        </td>

        <td data-label="الدافع"><?= esc($r['payer_name'] ?? '') ?></td>
        <td data-label="مصدر الدفع"><?= esc($r['payment_source'] ?? '') ?></td>

        <?php if(has_permission('expenses.processes')): ?>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionsExpense<?= $r['id'] ?>">
            <i class="bi bi-gear-fill"></i>
          </button>
          <!-- المودال -->
          <div class="modal fade" id="actionsExpense<?= $r['id'] ?>" tabindex="-1" aria-labelledby="actionsExpenseLabel<?= $r['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                  <h5 class="modal-title"><i class="bi bi-gear-fill me-1"></i> العمليات</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                  <?php if(has_permission('custodies.print')): ?>
                  <a class="btn btn-outline-primary w-100 mb-2" href="invoice_expense?id=<?= $r['id'] ?>"><i class="bi bi-printer me-2"></i> طباعة</a>
                  <?php endif ?>
                  <?php if(has_permission('custodies.edit')): ?>
                  <button class="btn btn-outline-warning w-100 mb-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#edit<?= $r['id'] ?>"><i class="bi bi-pencil me-2"></i> تعديل</button>
                  <?php endif ?>
                  <?php if(has_permission('custodies.delete')): ?>
                  <button class="btn btn-outline-danger w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $r['id'] ?>" data-name="<?= esc($r['main_expense']) ?>"><i class="bi bi-trash me-2"></i> حذف</button>
                  <?php endif ?>
                </div>
              </div>
            </div>
          </div>
        </td>
        <?php endif; ?>
      </tr>


<!-- مودال التعديل -->
<?php if(has_permission('expenses.edit')): ?>
<div class="modal fade" id="edit<?= $r['id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="expenses_edit" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">

        <div class="modal-header">
          <h5 class="modal-title">تعديل مصروف</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body vstack gap-3">
          <label>المصروفات</label>
          <select id="main_expense_edit<?= $r['id'] ?>" name="main_expense" class="form-select" required>
            <option value="">اختر</option>
            <option <?= $r['main_expense']=="ايجارات"?"selected":"" ?>>ايجارات</option>
            <option <?= $r['main_expense']=="حكومية"?"selected":"" ?>>حكومية</option>
            <option <?= $r['main_expense']=="مرافق وخدمات"?"selected":"" ?>>مرافق وخدمات</option>
            <option <?= $r['main_expense']=="رواتب"?"selected":"" ?>>رواتب</option>
            <option <?= $r['main_expense']=="سكن"?"selected":"" ?>>سكن</option>
            <option <?= $r['main_expense']=="مصروفات تشغيلية"?"selected":"" ?>>مصروفات تشغيلية</option>
            <option <?= $r['main_expense']=="مصروفات تأسيس"?"selected":"" ?>>مصروفات تأسيس</option>
            <option <?= $r['main_expense']=="مصروفات متنوعة"?"selected":"" ?>>مصروفات متنوعة</option>
          </select>

          <label>نوع المصروف</label>
          <div id="sub_expense_edit_wrapper<?= $r['id'] ?>"></div>
          <input type="hidden" name="sub_expense" id="hidden_sub_expense_<?= $r['id'] ?>" value="<?= esc($r['sub_expense']) ?>">

          <label>بيان المصروف</label>
          <input type="text" name="expense_desc" class="form-control" value="<?= esc($r['expense_desc']) ?>">

          <label>الدافع</label>
          <select name="payer_name" class="form-select payer-select" data-target="payment_source_edit<?= $r['id'] ?>">
            <option value="">اختر الدافع</option>
            <?php foreach(["شركة","مؤسسة","فيصل المطيري","بسام"] as $p): ?>
              <option value="<?= $p ?>" <?= ($r['payer_name']==$p)?"selected":"" ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>

          <label>مصدر الدفع</label>
          <select name="payment_source" id="payment_source_edit<?= $r['id'] ?>" class="form-select">
            <option value="">اختر مصدر الدفع</option>
            <option value="مالك" <?= ($r['payment_source']=='مالك')?'selected':'' ?>>مالك</option>
            <option value="بنك" <?= ($r['payment_source']=='بنك')?'selected':'' ?>>بنك</option>
            <option value="كاش" <?= ($r['payment_source']=='كاش')?'selected':'' ?>>كاش</option>
            <?php if(isset($custodies[$r['payer_name']])): ?>
              <option value="عهدة" <?= ($r['payment_source']=='عهدة')?'selected':'' ?>>عهدة (رصيد: <?= $custodies[$r['payer_name']] ?>)</option>
            <?php endif; ?>
          </select>

          <label>هل المصروف عليه ضريبة؟</label>
          <select id="has_vat_edit<?= $r['id'] ?>" name="has_vat" class="form-select" onchange="toggleVatSection('<?= $r['id'] ?>')">
            <option value="0" <?= ($r['has_vat']==0)?'selected':'' ?>>لا</option>
            <option value="1" <?= ($r['has_vat']==1)?'selected':'' ?>>نعم</option>
          </select>

          <label>قيمة المصروف</label>
          <input type="number" step="0.01" min="0" id="expense_amount_edit<?= $r['id'] ?>" name="expense_amount" class="form-control"
                 value="<?= esc($r['expense_amount']) ?>" placeholder="المبلغ" required
                 oninput="updateVatTotal('<?= $r['id'] ?>')">

          <div id="vat_section_edit<?= $r['id'] ?>" style="<?= $r['has_vat'] ? '' : 'display:none;' ?>">
            <label>نسبة الضريبة (٪)</label>
            <input type="number" step="0.01" id="vat_percent_edit<?= $r['id'] ?>" name="vat_percent" value="15" class="form-control" readonly>

            <label>إجمالي بعد الضريبة</label>
            <input type="text" id="total_with_vat_edit<?= $r['id'] ?>" class="form-control" readonly
                   value="<?= $r['has_vat'] ? number_format($r['total_amount'],2) : number_format($r['expense_amount'],2) ?>">
          </div>

          <label>المرفق</label>
          <label class="custom-file-upload w-100">
            <i class="bi bi-image"></i>
            <span id="file-text-edit-<?= $r['id'] ?>">اختر مرفق</span>
            <input type="file" name="expense_file" accept="image/*"
                   onchange="previewFile(this,'file-text-edit-<?= $r['id'] ?>','preview-edit-<?= $r['id'] ?>')">
            <?php if(!empty($r['expense_file'])): ?>
              <img id="preview-edit-<?= $r['id'] ?>" src="uploads/<?= esc($r['expense_file']) ?>" style="max-width:100px;margin-top:8px"/>
            <?php else: ?>
              <img id="preview-edit-<?= $r['id'] ?>" style="display:none;max-width:100px;margin-top:8px"/>
            <?php endif; ?>
          </label>
        </div>

        <div class="modal-footer">
          <button name="save" type="submit" class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

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

<!-- مودال الحذف -->
<?php if(has_permission('expenses.delete')): ?>
<!-- مودال واحد فقط -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">تأكيد الحذف</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        هل تريد حذف المصروف <strong id="expenseName"></strong>؟
      </div>
      <div class="modal-footer">
        <form method="post" action="expenses_delete">
          <input type="hidden" name="id" id="expenseId">
          <button type="submit" class="btn btn-danger">حذف</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<!-- مودال الإضافة -->
<?php if(has_permission('expenses.add')): ?>
<div class="modal fade" id="addExpense">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="expenses_add" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header"><h5 class="modal-title">إضافة مصروف</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body vstack gap-3">
          <label>رقم فاتورة المورد</label>
          <input type="number" name="bill_number" required class="form-control" placeholder="رقم فاتورة المورد">
          <label>المصروفات</label>
          <select id="main_expense" name="main_expense" class="form-select" required>
            <option value="">اختر</option>
            <option value="ايجارات">ايجارات</option>
            <option value="حكومية">حكومية</option>
            <option value="مرافق وخدمات">مرافق وخدمات</option>
            <option value="رواتب">رواتب</option>
            <option value="سكن">سكن</option>
            <option value="مصروفات تشغيلية">مصروفات تشغيلية</option>
            <option value="مصروفات تأسيس">مصروفات تأسيس</option>
            <option value="مصروفات متنوعة">مصروفات متنوعة</option>
          </select>

          <label>نوع المصروف</label>
          <div id="sub_expense_wrapper"></div>
          <input type="hidden" name="sub_expense" id="hidden_sub_expense_add" value="">

          <label>بيان المصروف</label>
          <input type="text" name="expense_desc" class="form-control" placeholder="ادخال شرح المصروف">

          <!--<label>قيمة المصروف</label>
          <input type="number" step="0.01" name="expense_amount" class="form-control" placeholder="المبلغ" required>-->

           <label>الدافع</label>
          <select name="payer_name" class="form-select payer-select" data-target="payment_source_add">
            <option value="">اختر الدافع</option>
            <option value="شركة">شركة</option>
            <option value="مؤسسة">مؤسسة</option>
            <option value="فيصل المطيري">فيصل المطيري</option>
            <option value="بسام">بسام</option>
          </select>

          <label>مصدر الدفع</label>
          <select name="payment_source" id="payment_source_add" class="form-select">
            <option value="">اختر مصدر الدفع</option>
            <option value="مالك">مالك</option>
            <option value="بنك">بنك</option>
            <option value="كاش">كاش</option>
          </select>

          <label>هل المصروف عليه ضريبة؟</label>
          <select id="has_vat" name="has_vat" class="form-select">
            <option value="0" selected>لا</option>
            <option value="1">نعم</option>
          </select>

          <label>قيمة المصروف</label>
          <input type="number" step="0.01" min="0" id="expense_amount" name="expense_amount" class="form-control" placeholder="المبلغ" required>

          <div id="vat_section" style="display:none;">
            <label>نسبة الضريبة (٪)</label>
            <input type="number" step="0.01" id="vat_percent" name="vat_percent" value="15" class="form-control" readonly>

            <label>إجمالي بعد الضريبة</label>
            <input type="text" id="total_with_vat" class="form-control" readonly>
          </div>


          <label>المرفق</label>
          <label class="custom-file-upload w-100">
            <i class="bi bi-image"></i>
            <span id="file-text-add">اختر مرفق</span>
            <input type="file" name="expense_file" accept="image/*" onchange="previewFile(this,'file-text-add','preview-add')">
            <img id="preview-add" style="display:none;max-width:100px;margin-top:8px"/>
          </label>
        </div>
        <div class="modal-footer"><button name="save" type="submit" class="btn btn-orange">حفظ</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(has_permission('expenses.add_group')): ?>
<div class="modal fade" id="addMultipleExpenses">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="expenses_add_multiple" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">إضافة مصروفات متعددة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>اسم الدافع</label>
              <select name="payer_name" class="form-select payer-select" data-target="payment_source_add1">
                <option hidden>اختر</option>
                <option>شركة</option>
                <option>مؤسسة</option>
                <option>فيصل المطيري</option>
                <option>بسام</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>مصدر الدفع</label>
              <select name="payment_source" id="payment_source_add1" class="form-select">
                <option hidden>اختر</option>
                <option>مالك</option>
                <option>كاش</option>
                <option>بنك</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
          <table class="table table-bordered" id="multipleExpensesTable">
          <thead>
            <tr>
              <th>#</th>
              <th>رقم الفاتورة</th>
              <th>تاريخ الفاتورة</th>
              <th>المصروف الرئيسي</th>
              <th>نوع المصروف</th>
              <th>بيان المصروف</th>
              <th>قيمة المصروف</th>
              <th>الضريبة</th>
              <th>الإجمالي بعد الضريبة</th>
              <th>حذف</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="row-index">1</td>
              <td><input type="number" name="invoice_serial[]" class="form-control" required></td>
              <td><input type="date" name="invoice_date[]" class="form-control" required></td>
              <td>
                <select name="main_expense[]" class="form-select main-expense-select" required>
                  <option value="">اختر</option>
                  <option value="ايجارات">ايجارات</option>
                  <option value="حكومية">حكومية</option>
                  <option value="مرافق وخدمات">مرافق وخدمات</option>
                  <option value="رواتب">رواتب</option>
                  <option value="سكن">سكن</option>
                  <option value="مصروفات تشغيلية">مصروفات تشغيلية</option>
                  <option value="مصروفات تأسيس">مصروفات تأسيس</option>
                  <option value="مصروفات متنوعة">مصروفات متنوعة</option>
                </select>
              </td>
              <td>
                <div class="sub-expense-wrapper"></div>
                <input type="hidden" name="sub_expense[]" class="hidden-sub-expense">
              </td>
              <td><input type="text" name="expense_desc[]" class="form-control"></td>
              <td><input type="number" step="0.01" min="0" name="expense_amount[]" class="form-control expense-amount"></td>
              <td>
                <select name="has_vat[]" class="form-select has-vat">
                  <option value="0">لا</option>
                  <option value="1">نعم</option>
                </select>
              </td>
              <td><input type="text" name="total_after_vat[]" class="form-control total-after-vat" readonly></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-row">حذف</button></td>
            </tr>
          </tbody>
          </table>
          </div>
          <button type="button" class="btn btn-outline-primary" id="addRowBtn">إضافة صف جديد</button>

          <hr>

          <div class="mt-4">
            <label>صورة الفاتورة</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-receipt"></i>
              <span id="file-text-expense-main"></span>
              <input type="file" name="invoice_image" accept="image/*"
                     onchange="previewFile(this,'file-text-expense-main','preview-expense-main')">
              <img id="preview-expense-main" style="display:none; max-width:150px; margin-top:10px"/>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-orange">حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(has_permission('expenses.addExpenseExcel')): ?>
<div class="modal fade" id="importExpensesModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="expenses_import" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">استيراد مصروفات من Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- حقول الدافع ومصدر الدفع خارج اختيار الملف -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label>الدافع</label>
              <select name="payer_name" class="form-select payer-select" data-target="payment_source_add2" required>
                <option value="">اختر الدافع</option>
                <option value="شركة">شركة</option>
                <option value="مؤسسة">مؤسسة</option>
                <option value="فيصل المطيري">فيصل المطيري</option>
                <option value="بسام">بسام</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>مصدر الدفع</label>
              <select name="payment_source" id="payment_source_add2" class="form-select" required>
                <option value="">اختر مصدر الدفع</option>
                <option value="مالك">مالك</option>
                <option value="بنك">بنك</option>
                <option value="كاش">كاش</option>
              </select>
            </div>
          </div>

          <!-- اختيار ملف Excel -->
          <div class="mb-3">
            <label>ملف Excel</label>
            <label class="custom-file-upload w-100">
              <i class="bi bi-cloud-arrow-up"></i>
              <span id="file-text-expense-excel">اختر ملف Excel</span>
              <input type="file" name="excel_file" accept=".xlsx,.xls" required
                     onchange="document.getElementById('file-text-expense-excel').textContent=this.files[0].name">
              <small class="text-muted">
              يجب أن يحتوي الملف على الأعمدة: invoice_serial, invoice_date, main_expense, sub_expense, expense_desc, expense_amount, has_vat
            </small>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-orange">استيراد</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const expenseTypes = {
  "ايجارات": ["أخرى"],
  "حكومية": ["إقامات ونقل كفالة","تأمينات","أخرى"],
  "مرافق وخدمات": ["كهرباء","مياه","غاز","هاتف وانترنت","أخرى"],
  "رواتب": ["رواتب موظفين","أخرى"],
  "سكن": ["سكن وإعاشة","كهرباء","مياه","أخرى"],
  "مصروفات متنوعة": ["أخرى"],
  "مصروفات تشغيلية": ["أخرى"],
  "مصروفات تأسيس": ["أخرى"]
};

// Helper: يقرأ القيمة الحالية داخل wrapper (select أو input)
function getCurrentSubVal(wrapper){
  const el = wrapper.querySelector('select, input');
  return el ? el.value : '';
}

// يرسم الحقل المناسب (select أو input) داخل الـ wrapper
function renderSubField(mainId, wrapperId, currentValue="", hiddenId){
  const main = document.getElementById(mainId);
  const wrapper = document.getElementById(wrapperId);
  const hidden = document.getElementById(hiddenId);
  if(!main || !wrapper || !hidden) return;

  const opts = expenseTypes[main.value] || [];
  wrapper.innerHTML = "";

  if(opts.length > 0){
    // إنشاء select
    const sel = document.createElement('select');
    sel.className = "form-select";
    sel.innerHTML = `<option value="">اختر</option>` + 
      opts.map(v=>`<option value="${v}" ${v===currentValue?'selected':''}>${v}</option>`).join('');
    wrapper.appendChild(sel);
    hidden.value = currentValue;  
    sel.addEventListener('change', function(){
      if(this.value === "أخرى"){
        // حوّل لحقل نصي
        wrapper.innerHTML = "";
        const input = document.createElement('input');
        input.type = "text";
        input.className = "form-control";
        input.placeholder = "ادخل نوع المصروف";
        wrapper.appendChild(input);
        input.focus();
        hidden.value = "";
        input.addEventListener('input', ()=> hidden.value = input.value);
      } else {
        hidden.value = this.value;
      }
    });
  } else {
    // إدخال نصي مباشرة
    const input = document.createElement('input');
    input.type = "text";
    input.className = "form-control";
    input.value = currentValue;
    wrapper.appendChild(input);
    hidden.value = currentValue;
    input.addEventListener('input', ()=> hidden.value = input.value);
  }
}

// إضافة listener للإضافة (add modal)
document.getElementById("main_expense")?.addEventListener("change", function(){
  // نحافظ على القيمة الحالية لو كان هناك input/select
  const wrapper = document.getElementById("sub_expense_wrapper");
  const cur = getCurrentSubVal(wrapper);
  renderSubField("main_expense","sub_expense_wrapper", cur);
});

// نهيئ مودالات التعديل بعد التحميل
const editRows = <?= json_encode($editRowsJs, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;

document.querySelectorAll('.modal').forEach(modal=>{
  modal.addEventListener('show.bs.modal', function(){
    const id = modal.querySelector('input[name="id"]')?.value;
    if(!id) return;
    const mainId = "main_expense_edit"+id;
    const wrapperId = "sub_expense_edit_wrapper"+id;
    const hiddenId = "hidden_sub_expense_"+id;
    const currentSub = editRows.find(x=>x.id==id)?.sub || '';
    renderSubField(mainId, wrapperId, currentSub, hiddenId);
  });
});

// مودال الإضافة
document.getElementById('main_expense')?.addEventListener('change', function(){
  renderSubField('main_expense','sub_expense_wrapper',
                 document.getElementById('hidden_sub_expense_add').value,
                 'hidden_sub_expense_add');
});


document.addEventListener("DOMContentLoaded", function(){
  // أولاً نهيئ كل مودال تعديل بقيمة الـ sub المخزنة
  editRows.forEach(row=>{
    const mainId = "main_expense_edit" + row.id;
    const wrapperId = "sub_expense_edit_wrapper" + row.id;
    // render initial
    renderSubField(mainId, wrapperId, row.sub);

    // عندما يغيّر المستخدم الخانة الأولى داخل المودال
    document.getElementById(mainId)?.addEventListener("change", function(){
      const wrapper = document.getElementById(wrapperId);
      const cur = getCurrentSubVal(wrapper);
      renderSubField(mainId, wrapperId, cur);
    });
  });

  // أيضاً نريد تهيئة الـ add modal لو كانت value موجودة مسبقاً (لمرة أولى)
  // لو احتجت تهيئة افتراضية هنا يمكن استدعاء renderSubField("main_expense","sub_expense_wrapper","")
});
  
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
<script>
// عند اختيار الدافع، تحديث مصدر الدفع
document.querySelectorAll('.payer-select').forEach(sel=>{
  sel.addEventListener('change',function(){
    let target = document.getElementById(this.dataset.target);
    if(!target) return;
    let payer = this.value;
    target.innerHTML = `
      <option value="">اختر مصدر الدفع</option>
      <option value="مالك">مالك</option>
      <option value="بنك">بنك</option>
      <option value="كاش">كاش</option>
    `;
    // لو عنده عهدة نضيفها
    let custodies = <?= json_encode($custodies) ?>;
    if(custodies[payer]){
      let opt = document.createElement('option');
      opt.value = "عهدة";
      opt.textContent = "عهدة (رصيد: " + custodies[payer] + ")";
      target.appendChild(opt);
    }
  });
});

// عند اختيار المصروف الرئيسي في الإضافة
const mainAdd = document.getElementById('main_expense');
if(mainAdd){
  mainAdd.addEventListener('change', function(){
    renderSubField('main_expense','sub_expense_wrapper',
                   document.getElementById('hidden_sub_expense_add').value,
                   'hidden_sub_expense_add');
  });

  // تهيئة افتراضية عند تحميل الصفحة (لو فيه قيمة محفوظة)
  renderSubField('main_expense','sub_expense_wrapper',
                 document.getElementById('hidden_sub_expense_add').value,
                 'hidden_sub_expense_add');
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const hasVatSelect = document.getElementById('has_vat');
  const expenseAmount = document.getElementById('expense_amount');
  const vatSection = document.getElementById('vat_section');
  const vatPercent = document.getElementById('vat_percent');
  const totalWithVat = document.getElementById('total_with_vat');

  function updateTotal() {
    const amount = parseFloat(expenseAmount.value) || 0;
    const vatRate = parseFloat(vatPercent.value) || 0;
    if (hasVatSelect.value === '1') {
      const total = amount + (amount * vatRate / 100);
      totalWithVat.value = total.toFixed(2);
    } else {
      totalWithVat.value = amount.toFixed(2);
    }
  }

  hasVatSelect.addEventListener('change', () => {
    if (hasVatSelect.value === '1') vatSection.style.display = 'block';
    else vatSection.style.display = 'none';
    updateTotal();
  });

  expenseAmount.addEventListener('input', updateTotal);
});
</script>
<script> function toggleVatSection(id){ const hasVat = document.getElementById('has_vat_edit'+id).value; document.getElementById('vat_section_edit'+id).style.display = hasVat == '1' ? 'block' : 'none'; updateVatTotal(id); } function updateVatTotal(id){ const amount = parseFloat(document.getElementById('expense_amount_edit'+id).value) || 0; const vatPercent = 15; const hasVat = document.getElementById('has_vat_edit'+id).value == '1'; const totalField = document.getElementById('total_with_vat_edit'+id); if(hasVat) totalField.value = (amount + (amount * vatPercent / 100)).toFixed(2); else totalField.value = amount.toFixed(2); } </script>
<?php require __DIR__.'/partials/footer.php'; ?>
<script>
  document.querySelectorAll('.modal').forEach(modal=>{
  modal.addEventListener('show.bs.modal', function(){
    const id = modal.querySelector('input[name="id"]')?.value;
    if(!id) return;
    const mainId = "main_expense_edit"+id;
    const wrapperId = "sub_expense_edit_wrapper"+id;
    const hiddenId = "hidden_sub_expense_"+id;
    const currentSub = editRows.find(x=>x.id==id)?.sub || '';
    renderSubField(mainId, wrapperId, currentSub, hiddenId);

    // listener لتغيير main_expense داخل المودال
    document.getElementById(mainId)?.addEventListener("change", function(){
      const wrapper = document.getElementById(wrapperId);
      const cur = getCurrentSubVal(wrapper);
      renderSubField(mainId, wrapperId, cur, hiddenId);
    });
  });
});

</script>
<script>
  var deleteModal = document.getElementById('deleteModal')
deleteModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget // الزر اللي ضغط عليه
  var id = button.getAttribute('data-id')
  var name = button.getAttribute('data-name')

  var modalTitle = deleteModal.querySelector('#expenseName')
  var modalIdInput = deleteModal.querySelector('#expenseId')

  modalTitle.textContent = name
  modalIdInput.value = id
})

</script>
<script>
  const expenseTypes_multiple = {
  "ايجارات": ["أخرى"],
  "حكومية": ["إقامات ونقل كفالة","تأمينات","أخرى"],
  "مرافق وخدمات": ["كهرباء","مياه","غاز","هاتف وانترنت","أخرى"],
  "رواتب": ["رواتب موظفين","أخرى"],
  "سكن": ["سكن وإعاشة","كهرباء","مياه","أخرى"],
  "مصروفات متنوعة": ["أخرى"],
  "مصروفات تشغيلية": ["أخرى"],
  "مصروفات تأسيس": ["أخرى"]
};

// إضافة صف جديد
document.getElementById('addRowBtn').addEventListener('click', function(){
  const table = document.getElementById('multipleExpensesTable').querySelector('tbody');
  const newRow = table.rows[0].cloneNode(true);
  // إعادة تهيئة القيم
  newRow.querySelectorAll('input, select').forEach(el=>{
    el.value = '';
    if(el.type==='file') el.value = null;
  });
  table.appendChild(newRow);
  updateRowIndices();
});

// حذف صف
document.getElementById('multipleExpensesTable').addEventListener('click', function(e){
  if(e.target.classList.contains('remove-row')){
    const tr = e.target.closest('tr');
    tr.remove();
    updateRowIndices();
  }
});

// تحديث أرقام الصفوف
function updateRowIndices(){
  document.querySelectorAll('#multipleExpensesTable tbody tr').forEach((tr,i)=>{
    tr.querySelector('.row-index').textContent = i+1;
  });
}

// ربط dynamic sub_expense
document.getElementById('multipleExpensesTable').addEventListener('change', function(e){
  if(e.target.classList.contains('main-expense-select')){
    const wrapper = e.target.closest('td').nextElementSibling.querySelector('.sub-expense-wrapper');
    const hidden = e.target.closest('td').nextElementSibling.querySelector('.hidden-sub-expense');
    const opts = expenseTypes_multiple[e.target.value] || [];
    wrapper.innerHTML = "";
    if(opts.length>0){
      const sel = document.createElement('select');
      sel.className = 'form-select';
      sel.innerHTML = `<option value="">اختر</option>`+opts.map(v=>`<option value="${v}">${v}</option>`).join('');
      wrapper.appendChild(sel);
      hidden.value='';
      sel.addEventListener('change', function(){
        if(this.value==='أخرى'){
          const input = document.createElement('input');
          input.type='text';
          input.className='form-control';
          wrapper.innerHTML='';
          wrapper.appendChild(input);
          input.addEventListener('input', ()=>hidden.value = input.value);
        } else hidden.value = this.value;
      });
    } else {
      const input = document.createElement('input');
      input.type='text';
      input.className='form-control';
      wrapper.appendChild(input);
      input.addEventListener('input', ()=>hidden.value = input.value);
    }
  }
});

// دالة لحساب الإجمالي بعد الضريبة
function calculateTotal(row) {
  const amountInput = row.querySelector('.expense-amount');
  const vatSelect = row.querySelector('.has-vat');
  const totalField = row.querySelector('.total-after-vat');

  const amount = parseFloat(amountInput.value) || 0;
  const hasVat = vatSelect.value === '1';

  const total = hasVat ? amount * 1.15 : amount;
  totalField.value = total.toFixed(2);
}

// ربط الأحداث عند الكتابة أو تغيير القيمة
document.getElementById('multipleExpensesTable').addEventListener('input', function(e){
  if (e.target.classList.contains('expense-amount')) {
    const row = e.target.closest('tr');
    calculateTotal(row);
  }
});

document.getElementById('multipleExpensesTable').addEventListener('change', function(e){
  if (e.target.classList.contains('has-vat')) {
    const row = e.target.closest('tr');
    calculateTotal(row);
  }
});
</script>