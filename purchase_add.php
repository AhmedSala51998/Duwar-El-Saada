<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }

    $names = $_POST['name'] ?? [];
    $units = $_POST['unit'] ?? [];
    /*$payers = $_POST['payer_name'] ?? [];
    $sources = $_POST['payment_source'] ?? [];*/
    $quantities = $_POST['quantity'] ?? [];
    $single_packages = $_POST['single_package'] ?? [];
    $prices = $_POST['price'] ?? [];
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');
    $bill_number = trim($_POST['bill_number'] ?? '');
    $packages = $_POST['package'] ?? [];
    $payer    = trim($_POST['payer_name'] ?? '');
    $payment_source = trim($_POST['payment_source'] ?? '');

    // ✅ تحقق من الرقم الضريبي (15 رقم بالضبط) 
    if (!preg_match('/^\d{15}$/', $tax_number)) {
         $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ الرقم الضريبي يجب أن يكون 15 رقم بالضبط.'];
          header('Location: ' . BASE_URL . '/purchases.php'); exit; 
        } // ✅ تحقق من التكرار في قاعدة البيانات 
        $checkTax = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE bill_number = ?");
         $checkTax->execute([$bill_number]);
          if ($checkTax->fetchColumn() > 0) { 
            $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ رقم الفاتورة مستخدم بالفعل في فاتورة سابقة.'];
             header('Location: ' . BASE_URL . '/purchases.php'); exit; 
          }

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
    if ($lastSerial && preg_match('/DAELP(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    } else {
        $nextNumber = 1;
    }
    $serial_invoice = "DAELP" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    // رفع صورة الفاتورة العامة (مش لكل صف)
    $invoiceImage = upload_image('invoice_image');

    // إدخال بيانات الفاتورة
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders_purchases (bill_number , tax_number , invoice_number, invoice_serial, supplier_name, total, vat, all_total, created_at, invoice_image)
        VALUES (? , ? , ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtOrder->execute([$bill_number , $tax_number , $invoice_number, $serial_invoice, $supplier_name, $total, $vat, $all_total, $created_at, $invoiceImage]);

    $order_id = $pdo->lastInsertId();

    // إدخال تفاصيل الأصناف
    foreach ($names as $i => $name) {
        $name = trim($name);
        if (!$name) continue;

        $unit = $units[$i] ?? '';
        $package = trim($packages[$i] ?? '');
        /*$payer = trim($payers[$i] ?? '');
        $payment_source = $sources[$i] ?? 'كاش';*/
        $quantity = (float)($quantities[$i] ?? 0);
        $single_package = (float)($single_packages[$i] ?? 0);
        $unit_quantity = $quantity * $single_package;
        $price = (float)($prices[$i] ?? 0);
        $unit_price = $price / $single_package;

        $vatRate = 0.15;
        $subtotal_unit = $quantity * $price;
        $vat_unit = $subtotal_unit * $vatRate;
        $alltotal_unit = $subtotal_unit + $vat_unit;

        $stmt = $pdo->prepare("
            INSERT INTO purchases (name, quantity, prinitng_quantity , single_package , total_packages, unit, package, price , total_price, payer_name, payment_source , unit_total ,unit_vat ,unit_all_total, order_id)
            VALUES (?, ?,?,?, ?, ?, ?,?, ?, ?, ?, ?, ? ,?,?)
        ");
        $stmt->execute([$name, $unit_quantity , $unit_quantity , $single_package , $quantity, $unit, $package,$unit_price, $price, $payer, $payment_source , $subtotal_unit , $vat_unit , $alltotal_unit, $order_id]);
        $purchase_id = $pdo->lastInsertId();

        // خصم من العهدة لو كانت وسيلة الدفع "عهدة"
        if ($payment_source === 'عهدة') {
            $amountNeeded = ($price * $quantity) + $vat_unit;

            // جلب كل العهد المتاحة للشخص بالترتيب من الأقدم للأحدث
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
            $stmtC->execute([$payer]);
            $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

            foreach ($custodies as $custody) {
                if ($amountNeeded <= 0) break;

                if ($custody['amount'] >= $amountNeeded) {
                    $newAmount = $custody['amount'] - $amountNeeded;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                    // سجل المعاملة
                    $stmtTx = $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmtTx->execute(['purchase', $purchase_id ?? 0, $custody['id'], $amountNeeded]);

                    $amountNeeded = 0;
                } else {
                    $amountToUse = $custody['amount'];
                    $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);

                    // سجل المعاملة
                    $stmtTx = $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmtTx->execute(['purchase', $purchase_id ?? 0, $custody['id'], $amountToUse]);

                    $amountNeeded -= $amountToUse;
                }
            }

            if ($amountNeeded > 0) {
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
