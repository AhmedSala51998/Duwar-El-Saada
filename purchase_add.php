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
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');

    // حساب الإجمالي
    $total = 0;
    foreach ($names as $i => $name) {
        $q = (float)($quantities[$i] ?? 0);
        $p = (float)($prices[$i] ?? 0);
        $total += $q * $p;
    }

    $vat = round($total * 0.15, 2);
    $all_total = $total + $vat;
    $created_at = $_POST['invoice_date'] ? $_POST['invoice_date'] . ' ' . date('H:i:s') : date('Y-m-d H:i:s');

    // رقم الفاتورة العشوائي الفريد
    do {
        $invoice_number = rand(100000, 999999);
        $checkInvoice = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE invoice_number=?");
        $checkInvoice->execute([$invoice_number]);
        $exists = $checkInvoice->fetchColumn();
    } while($exists > 0);

    // إنشاء رقم تسلسلي للفواتير بصيغة DAEL00001
    $lastSerial = $pdo->query("SELECT invoice_serial FROM orders_purchases ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($lastSerial && preg_match('/DAEL(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    } else {
        $nextNumber = 1;
    }
    $serial_invoice = "DAEL" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    // رفع صورة الفاتورة العامة (مش لكل صف)
    $invoiceImage = upload_image('invoice_image');

    // إدخال بيانات الفاتورة
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders_purchases (tax_number , invoice_number, invoice_serial, supplier_name, total, vat, all_total, created_at, invoice_image)
        VALUES (? , ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtOrder->execute([$tax_number , $invoice_number, $serial_invoice, $supplier_name, $total, $vat, $all_total, $created_at, $invoiceImage]);

    $order_id = $pdo->lastInsertId();

    // إدخال تفاصيل الأصناف
    foreach ($names as $i => $name) {
        $name = trim($name);
        if (!$name) continue;

        $unit = $units[$i] ?? '';
        $payer = trim($payers[$i] ?? '');
        $payment_source = $sources[$i] ?? 'كاش';
        $quantity = (float)($quantities[$i] ?? 0);
        $price = (float)($prices[$i] ?? 0);

        $stmt = $pdo->prepare("
            INSERT INTO purchases (name, quantity, unit, price, payer_name, payment_source, order_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $quantity, $unit, $price, $payer, $payment_source, $order_id]);

        // خصم من العهدة لو كانت وسيلة الدفع "عهدة"
        if ($payment_source === 'عهدة') {
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
            $stmtC->execute([$payer]);
            $custody = $stmtC->fetch();

            $amountNeeded = $price * $quantity;
            if ($custody && $custody['amount'] >= $amountNeeded) {
                $newAmount = $custody['amount'] - $amountNeeded;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            } else {
                $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي للشخص: ' . htmlspecialchars($payer)];
                header('Location: ' . BASE_URL . '/purchases.php');
                exit;
            }
        }
    }

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => "✅ تم حفظ الفاتورة رقم {$serial_invoice} ورصد جميع الأصناف بنجاح"
    ];
    header('Location: ' . BASE_URL . '/purchases.php');
    exit;
}

// دالة رفع الصور العامة
function upload_image($field) {
    if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES[$field]['tmp_name'];
        $fileName = time() . "_" . basename($_FILES[$field]['name']);
        $target = __DIR__ . "/uploads/" . $fileName;
        move_uploaded_file($fileTmp, $target);
        return $fileName;
    }
    return null;
}
?>
