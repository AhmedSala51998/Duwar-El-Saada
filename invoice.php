<?php 
require __DIR__.'/partials/header.php'; 

// جلب الـ purchase من الرابط
$purchaseId = (int)($_GET['id'] ?? 0);
$purchaseStmt = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
$purchaseStmt->execute([$purchaseId]);
$purchase = $purchaseStmt->fetch();

if (!$purchase) { 
    echo "<div class='alert alert-warning'>المشتريات غير موجودة</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}

// التحقق من وجود فاتورة مرتبطة
$orderId = $purchase['order_id'] ?? null;
if ($orderId) {
    $orderStmt = $pdo->prepare("SELECT * FROM orders_purchases WHERE id=?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();

    // جلب كل المشتريات المرتبطة بنفس الفاتورة
    $itemsStmt = $pdo->prepare("SELECT * FROM purchases WHERE order_id=?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();
} else {
     echo "<div class='alert alert-warning'>الفاتورة غير موجودة</div>"; 
    require __DIR__.'/partials/footer.php'; 
    exit; 
}
?>


<style>
@media print {
  body * { visibility: hidden; }
  .print-area, .print-area * { visibility: visible; }
  .print-area { position: absolute; left: 0; top: 0; width: 100%; }
}

.print-area {
  max-width: 900px; margin: auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  border: 1px solid #ccc; padding: 20px; border-radius: 8px; background: #fff;
}

.print-area h2 { margin-bottom: 10px; }
.print-area table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.print-area table th, .print-area table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
.print-area table th { background-color: #f2f2f2; }
.print-area img.logo { width: 80px; }
.text-muted { color: #888; }
</style>

<div class="d-print-none mb-3">
  <button onclick="window.print()" class="btn btn-orange"><i class="bi bi-printer"></i> طباعة</button>
</div>

<div class="print-area">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <img src="assets/logo.svg" class="logo" alt="Logo">
      <h2>فاتورة مشتريات</h2>
    </div>
    <div class="text-end">
      <div><strong>المورد:</strong> <?= esc($order['supplier_name']) ?></div>
      <div><strong>رقم الفاتورة:</strong> <?= esc($order['invoice_number']) ?></div>
      <div><strong>التاريخ:</strong> <?= esc($order['created_at']) ?></div>
    </div>
  </div>

  <hr>

  <table>
    <thead>
      <tr>
        <th>الصنف</th>
        <th>الكمية</th>
        <th>الوحدة</th>
        <th>السعر</th>
        <th>صورة المنتج</th>
        <th>فاتورة المنتج</th>
        <th>الدافع</th>
        <th>مصدر الدفع</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $item): ?>
      <tr>
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['quantity']) ?></td>
        <td><?= esc($item['unit']) ?></td>
        <td><?= number_format((float)$item['price'],2) ?> ريال</td>
        <td>
          <?php if($item['product_image']): ?>
            <img src="uploads/<?= esc($item['product_image']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
          <?php endif; ?>
        </td>
        <td>
          <?php if($item['invoice_image']): ?>
            <img src="uploads/<?= esc($item['invoice_image']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
          <?php endif; ?>
        </td>
        <td><?= esc($item['payer_name']) ?></td>
        <td><?= esc($item['payment_source']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="mt-3 text-end">
    <?php 
      $total = array_sum(array_map(fn($i) => $i['quantity']*$i['price'], $items));
      $vat = $total * 0.15;
      $allTotal = $total + $vat;
    ?>
    <div><strong>المجموع:</strong> <?= number_format($total,2) ?> ريال</div>
    <div><strong>الضريبة 15%:</strong> <?= number_format($vat,2) ?> ريال</div>
    <div><strong>الإجمالي بعد الضريبة:</strong> <?= number_format($allTotal,2) ?> ريال</div>
  </div>
</div>

<?php require __DIR__.'/partials/footer.php'; ?>
