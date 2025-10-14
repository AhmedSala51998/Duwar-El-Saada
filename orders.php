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
<style>
  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
</style>
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
$can_edit = in_array(current_role(), ['admin','manager']);

$stocks = $pdo->query("
    SELECT 
        p.id,
        p.name, 
        p.unit,
        SUM(p.quantity) - IFNULL(SUM(o.total_used),0) as remaining_qty
    FROM purchases p
    LEFT JOIN (
        SELECT purchase_id, SUM(qty) as total_used
        FROM orders
        GROUP BY purchase_id
    ) o ON o.purchase_id = p.id
    GROUP BY p.id, p.name, p.unit
    ORDER BY p.name
")->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="d-flex flex-wrap gap-3 mb-4">
<?php foreach($stocks as $s): ?>
    <div class="card text-center shadow-sm" style="width: 160px; border-radius: 15px; background: #fff8e1; transition: transform 0.2s;">
        <div class="card-body p-2">
            <h6 class="card-title mb-1"><?= esc($s['name']) ?></h6>
            <p class="card-text mb-0"><strong><?= $s['remaining_qty'] ?></strong> <?= esc($s['unit']) ?></p>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h3 class="mb-0">أوامر التشغيل</h3>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="kw" placeholder="بحث باسم المنتج" value="<?= esc($kw) ?>">
      <button class="btn btn-outline-secondary">بحث</button>
    </form>
    <a class="btn btn-outline-dark" href="export_orders_excel.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    <a class="btn btn-outline-dark" href="export_orders_pdf.php?kw=<?= urlencode($kw) ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <?php if($can_edit): ?>
      <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addOrder">
        <i class="bi bi-plus-lg"></i> إنشاء أمر
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="table-responsive">
<table class="table table-hover">
  <thead class="table-light">
    <tr>
      <th>#</th><th>المنتج</th><th>الكمية</th><th>الوحدة</th><th>ملاحظة</th><th>التاريخ</th>
      <?php if($can_edit): ?><th>حذف</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($orders as $o): ?>
    <tr>
      <td><?= $o['id'] ?></td>
      <td><?= esc($o['pname']) ?></td>
      <td><?= $o['qty'] ?></td>
      <td><?= esc($o['unit']) ?></td>
      <td><?= esc($o['note']) ?></td>
      <td><?= esc($o['created_at']) ?></td>
      <?php if($can_edit): ?>
        <td>
          <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#del<?= $o['id'] ?>">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      <?php endif; ?>
    </tr>

    <?php if($can_edit): ?>
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

<?php if($can_edit): ?>
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

<?php require __DIR__.'/partials/footer.php'; ?>
