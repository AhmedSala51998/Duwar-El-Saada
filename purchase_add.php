<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $names = $_POST['name'] ?? [];
    $units = $_POST['unit'] ?? [];
    $payers = $_POST['payer_name'] ?? [];
    $sources = $_POST['payment_source'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];
    $supplier_name = trim($_POST['supplier_name'] ?? ''); // من حقل الإدخال للفاتورة

    // حساب المجموع الكلي قبل إدخال الصفوف
    $total = 0;
    foreach ($names as $i => $name) {
        $q = (float)($quantities[$i] ?? 0);
        $p = (float)($prices[$i] ?? 0);
        $total += $q * $p;
    }

    $vat = $total * 0.15; // 15%
    $all_total = $total + $vat;
    $created_at = $_POST['invoice_date'] ? $_POST['invoice_date'] . ' ' . date('H:i:s') : date('Y-m-d H:i:s');

    // إنشاء رقم فاتورة فريد
    do {
        $invoice_number = rand(100000, 999999);
        $checkInvoice = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE invoice_number=?");
        $checkInvoice->execute([$invoice_number]);
        $exists = $checkInvoice->fetchColumn();
    } while($exists > 0);

    // إدخال الفاتورة في orders_purchases
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders_purchases (invoice_number, supplier_name, total, vat, all_total, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtOrder->execute([$invoice_number, $supplier_name, $total, $vat, $all_total, $created_at]);

    // الحصول على id الفاتورة الجديدة
    $order_id = $pdo->lastInsertId();

    // إدخال كل المشتريات وربطها بالـ order_id
    foreach ($names as $i => $name) {
        $name = trim($name);
        $unit = $units[$i] ?? '';
        $payer = trim($payers[$i] ?? '');
        $payment_source = $sources[$i] ?? 'كاش';
        $quantity = (float)($quantities[$i] ?? 0);
        $price = (float)($prices[$i] ?? 0);

        if (!$name || !$unit) continue; // تجاهل الصفوف الفارغة

        // رفع الصور
        $productImage = upload_image('product_image', $i);
        $invoiceImage = upload_image('invoice_image', $i);

        $stmt = $pdo->prepare("
            INSERT INTO purchases 
            (name, quantity, unit, price, product_image, invoice_image, payer_name, payment_source, order_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $quantity,
            $unit,
            $price,
            $productImage,
            $invoiceImage,
            $payer,
            $payment_source,
            $order_id
        ]);
    }

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => "✅ تم حفظ الفاتورة ورصد جميع الأصناف"
    ];
    header('Location: ' . BASE_URL . '/purchases.php');
    exit;
}

// دالة رفع الصور كما لديك
function upload_image($field, $index = null) {
    if ($index !== null) {
        if (isset($_FILES[$field]['name'][$index]) && $_FILES[$field]['error'][$index] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$field]['tmp_name'][$index];
            $fileName = time() . "_" . basename($_FILES[$field]['name'][$index]);
            $target = __DIR__ . "/uploads/" . $fileName;
            move_uploaded_file($fileTmp, $target);
            return $fileName;
        }
        return null;
    } else {
        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$field]['tmp_name'];
            $fileName = time() . "_" . basename($_FILES[$field]['name']);
            $target = __DIR__ . "/uploads/" . $fileName;
            move_uploaded_file($fileTmp, $target);
            return $fileName;
        }
        return null;
    }
}
