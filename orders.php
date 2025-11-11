<?php require __DIR__.'/partials/header.php'; require_permission('orders.view'); ?>
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
$items = $pdo->query("SELECT * FROM purchases ORDER BY name")->fetchAll();
$kw = trim($_GET['kw'] ?? '');
$q = "SELECT o.*, p.name pname, p.unit punit 
      FROM orders o 
      JOIN purchases p ON p.id=o.purchase_id 
      WHERE 1";
$params=[];
if($kw!==''){ 
    $q.=" AND p.name LIKE ?"; 
    $params[]="%$kw%"; 
}
$q.=" ORDER BY o.id DESC";
$s=$pdo->prepare($q); 
$s->execute($params);
$orders=$s->fetchAll();
//$can_edit = in_array(current_role(), ['admin','manager']);

/*$stocks = $pdo->query("
    SELECT 
        name,
        unit,
        SUM(quantity) AS total_qty
    FROM purchases
    GROUP BY name, unit
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);*/
/*$stocks = $pdo->query("
    SELECT 
        id,
        name,
        unit,
        SUM(quantity) AS total_qty,
        MAX(created_at) AS last_added
    FROM purchases
    GROUP BY name, unit
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);*/
$stocks = $pdo->query("
    SELECT 
        MIN(id) AS id,
        name,
        unit,
        SUM(quantity) AS total_qty,
        MAX(created_at) AS last_added
    FROM purchases
    GROUP BY 
        TRIM(REPLACE(REPLACE(LOWER(name), ' ', ''), ' ', '')),
        TRIM(REPLACE(REPLACE(LOWER(unit), ' ', ''), ' ', ''))
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!--<div class="d-flex flex-wrap justify-content-start gap-3 mb-4">
<?php foreach($stocks as $s): ?>
    <div class="stock-card text-center">
        <div class="stock-quantity">
            <span><?= number_format($s['total_qty'], 2) ?></span>
            <small><?= esc($s['unit']) ?></small>
        </div>
        <div class="stock-info">
            <h6 class="stock-name"><?= esc($s['name']) ?></h6>
        </div>
    </div>
<?php endforeach; ?>
</div>-->

<style>
.stock-card {
    width: 180px;
    min-height: 140px;
    background: linear-gradient(135deg, #ff6a00, #ff9f43);
    border-radius: 18px;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    padding: 16px 10px;
    position: relative;
    overflow: visible;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.stock-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.25);
}

.stock-quantity {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    width: 80px;
    height: 80px;
    margin: 0 auto 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    font-size: 20px;
    color: #fff;
    box-shadow: inset 0 0 10px rgba(255,255,255,0.3);
    transition: all 0.3s ease;
    position: relative;
}

/* إضافة نبض / شعاع عند hover */
.stock-quantity::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    top: 0;
    left: 0;
    box-shadow: 0 0 0 rgba(255,255,255,0.7);
    transition: all 0.6s ease-out;
}

.stock-quantity:hover::after {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255,255,255,0.7);
    }
    50% {
        transform: scale(1.3);
        box-shadow: 0 0 20px 10px rgba(255,255,255,0.3);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255,255,255,0);
    }
}

.stock-quantity small {
    font-size: 12px;
    opacity: 0.9;
}

.stock-info {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 6px 8px;
}

.stock-name {
    font-size: 15px;
    font-weight: 600;
    margin: 0;
    word-wrap: break-word;
    white-space: normal;
}

/* نخلي مربع البحث تحت العناصر بدل فوقها */
.select2-search--dropdown {
  order: 2;
}
.select2-results {
  order: 1;
  margin-bottom: 5px;
}
.select2-dropdown--below {
  display: flex;
  flex-direction: column-reverse;
}
/* تكبير ارتفاع المربع الأساسي */
.select2-container .select2-selection--single {
  height: 48px !important; /* الارتفاع */
  display: flex !important;
  align-items: center !important;
  font-size: 16px; /* تكبير الخط شوية */
}

/* تكبير السهم والمسافة الداخلية */
.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 48px !important;
  padding-left: 10px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 48px !important;
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


<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h3 class="page-title mb-0">
    <span class="stat-icon"><i class="bi bi-gear"></i></span>
    أوامر التشغيل
  </h3>

  <div class="page-actions d-flex flex-wrap gap-2 justify-content-end align-items-center">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث باسم المنتج" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>

    <?php if(has_permission('orders.print_excel')): ?>
      <a class="btn btn-outline-dark" href="export_orders_excel.php?kw=<?= urlencode($kw) ?>">
        <i class="bi bi-file-earmark-spreadsheet"></i>
        <span class="d-none d-sm-inline">Excel</span>
      </a>
    <?php endif; ?>

    <?php if(has_permission('orders.print_pdf')): ?>
      <a class="btn btn-outline-dark" href="export_orders_pdf.php?kw=<?= urlencode($kw) ?>">
        <i class="bi bi-filetype-pdf"></i>
        <span class="d-none d-sm-inline">PDF</span>
      </a>
    <?php endif; ?>

    <?php if(has_permission('orders.view_inventory')): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#stocksModal">
        <i class="bi bi-box-seam"></i>
        <span class="d-none d-sm-inline">المخزون</span>
      </button>
    <?php endif; ?>

    <?php if(has_permission('orders.add')): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addOrder">
        <i class="bi bi-plus-lg"></i>
        <span class="d-none d-sm-inline">إنشاء أمر</span>
      </button>
    <?php endif; ?>

    <?php if(has_permission('orders.add_group')): ?>
      <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMultipleOrders">
        <i class="bi bi-layers"></i>
        <span class="d-none d-sm-inline">إضافة متعددة</span>
      </button>
    <?php endif; ?>

  </div>
</div>


<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
  <table class="table table-hover align-middle mb-0 custom-table">
    <thead class="table-light border-bottom border-2 small-header text-center text-secondary fw-semibold">
      <tr>
        <th>#</th>
        <th>المنتج</th>
        <th>الكمية</th>
        <th>الوحدة</th>
        <th>ملاحظة</th>
        <th>التاريخ</th>
        <?php if(has_permission('orders.processes')): ?><th>حذف</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($orders as $o): ?>
      <tr class="text-center">
        <td data-label="#" class="fw-bold text-muted"><?= $o['id'] ?></td>
        <td data-label="المنتج"><?= esc($o['pname']) ?></td>
        <td data-label="الكمية"><?= $o['qty'] ?></td>
        <td data-label="الوحدة"><?= esc($o['unit']) ?></td>
        <td data-label="ملاحظة"><?= esc($o['note']) ?></td>
        <td data-label="التاريخ"><?= esc($o['created_at']) ?></td>
        <?php if(has_permission('orders.processes')): ?>
        <td class="text-center">
          <?php if(has_permission('orders.delete')): ?>
          <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#del<?= $o['id'] ?>">
            <i class="bi bi-trash"></i>
          </button>
          <?php endif ?>
        </td>
        <?php endif; ?>
      </tr>

    <?php if(has_permission('orders.delete')): ?>
    <!-- Modal تأكيد الحذف -->
    <div class="modal fade" id="del<?= $o['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">تأكيد الحذف</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            هل أنت متأكد أنك تريد حذف أمر التشغيل رقم <b><?= $o['id'] ?></b>  
            للمنتج <b><?= esc($o['pname']) ?></b> واسترجاع الكمية؟
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <a href="order_delete?id=<?= $o['id'] ?>" class="btn btn-danger">حذف</a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- ✅ Modal عرض المخزون -->
<div class="modal fade" id="stocksModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #ff6a00, #ff9f43); color: #fff;">
        <h5 class="modal-title"><i class="bi bi-box-seam"></i> المخزون الحالي</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- 🔍 مربع البحث -->
        <div class="mb-3">
          <input type="text" id="stockSearch" class="form-control" placeholder="ابحث عن منتج...">
        </div>

        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-bordered align-middle mb-0" id="stocksTable">
            <thead class="table-light">
              <tr>
                <th>اسم الصنف</th>
                <th>الكمية</th>
                <th>الوحدة</th>
                <th>تاريخ آخر إضافة</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($stocks as $s): ?>
              <tr class="clickable-row"  data-href="invoice?id=<?= $s['id'] ?>&highlight=<?= urlencode(trim($s['name'])) ?>" title="عرض فاتورة <?= esc($s['name']) ?>">
                <td><?= esc($s['name']) ?></td>
                <td><?= number_format($s['total_qty'], 2) ?></td>
                <td><?= esc($s['unit']) ?></td>
                <td><?= esc($s['last_added']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".clickable-row").forEach(row => {
      row.addEventListener("click", function() {
        window.location = this.dataset.href;
      });
    });
  });
</script>

<style>
.clickable-row {
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}
.clickable-row:hover {
  background-color: rgba(255, 154, 67, 0.15);
}
</style>


<?php if(has_permission('orders.add')): ?>
<div class="modal fade" id="addOrder"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="order_add">
    <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
    <div class="modal-header">
      <h5 class="modal-title">إنشاء أمر تشغيل</h5>
      <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body vstack gap-3">
      <div>
        <label class="form-label">المنتج</label>
        <select name="purchase_id" class="form-select">
          <?php foreach($items as $i): ?>
            <option value="<?= $i['id'] ?>">
              <?= esc($i['name']) ?> — متاح: <?= $i['quantity'].' '.$i['unit'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label">الكمية</label>
        <input type="number" step="0.001" class="form-control" name="qty" required>
      </div>
      <div>
        <label class="form-label">الوحدة</label>
        <select name="unit" class="form-select">
          <option>عدد</option>
          <option>جرام</option>
          <option>كيلو</option>
          <option>لتر</option>
        </select>
      </div>
      <div>
        <label class="form-label">ملاحظة</label>
        <input name="note" class="form-control">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-orange">تنفيذ</button>
    </div>
  </form>
</div></div></div>
<?php endif; ?>

<?php if(has_permission('orders.add_group')): ?>
<div class="modal fade" id="addMultipleOrders">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="post" action="orders_add_multiple">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

        <div class="modal-header">
          <h5 class="modal-title">إضافة أوامر تشغيل متعددة</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-bordered align-middle" id="ordersTable">
              <thead class="table-light">
                <tr>
                  <th>المنتج</th>
                  <th>الكمية</th>
                  <th>الوحدة</th>
                  <th>ملاحظة</th>
                  <th width="60">إجراء</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select name="orders[0][purchase_id]" class="form-select select2-product" required>
                      <option value="">اختر المنتج</option>
                      <?php foreach($items as $i): ?>
                        <option value="<?= $i['id'] ?>">
                          <?= esc($i['name']) ?> — متاح: <?= $i['quantity'].' '.$i['unit'] ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><input type="number" step="0.001" name="orders[0][qty]" class="form-control" required></td>
                  <td>
                    <select name="orders[0][unit]" class="form-select">
                      <option>عدد</option>
                      <option>جرام</option>
                      <option>كيلو</option>
                      <option>لتر</option>
                    </select>
                  </td>
                  <td><input type="text" name="orders[0][note]" class="form-control"></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn btn-outline-primary mt-2" id="addRow">
            <i class="bi bi-plus-circle"></i> إضافة صف جديد
          </button>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-orange">تنفيذ جميع الأوامر</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
  $('select[name="purchase_id"]').select2({
    width: '100%',
    placeholder: 'ابحث عن المنتج...',
    allowClear: true,
    dropdownParent: $('#addOrder')
  });
});
</script>
<script>
// 📌 ننفذ الكود بعد تحميل الصفحة بالكامل
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("stockSearch");

  // 📌 نضيف الحدث لما يكتب المستخدم
  searchInput?.addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    const table = document.getElementById("stocksTable");
    if (!table) return; // لو الجدول مش موجود نخرج بهدوء

    const trs = table.querySelectorAll("tbody tr");
    trs.forEach(tr => {
      const text = tr.cells[0]?.innerText.toLowerCase() || "";
      tr.style.display = text.includes(filter) ? "" : "none";
    });
  });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  let index = 1;

  // ✅ إضافة صف جديد
  document.getElementById("addRow").addEventListener("click", function () {
    const tbody = document.querySelector("#ordersTable tbody");
    const newRow = tbody.rows[0].cloneNode(true);

    // تحديث أسماء الحقول بالـ index الجديد
    newRow.querySelectorAll("select, input").forEach(el => {
      el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
      el.value = "";
    });

    tbody.appendChild(newRow);
    index++;

    // تفعيل select2 في الصف الجديد
    $(newRow).find(".select2-product").select2({
      width: '100%',
      dropdownParent: $('#addMultipleOrders')
    });
  });

  // ✅ حذف صف
  document.addEventListener("click", function (e) {
    if (e.target.closest(".removeRow")) {
      const rows = document.querySelectorAll("#ordersTable tbody tr");
      if (rows.length > 1) e.target.closest("tr").remove();
    }
  });

  // ✅ تفعيل select2 أول مرة
  $('.select2-product').select2({
    width: '100%',
    dropdownParent: $('#addMultipleOrders')
  });
});
</script>
