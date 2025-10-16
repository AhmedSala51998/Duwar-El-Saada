<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    /*if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }*/

    // التحقق من ملف الإكسل
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ لم يتم رفع ملف Excel بشكل صحيح'];
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }

    require_once __DIR__ . '/libs/SimpleXLSX.php';
    $filePath = $_FILES['excel_file']['tmp_name'];

    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');
    $bill_number = trim($_POST['bill_number'] ?? '');
    $invoice_date = trim($_POST['invoice_date'] ?? date('Y-m-d'));
    $payer_name = trim($_POST['payer_name'] ?? '');
    $payment_source = trim($_POST['payment_source'] ?? '');

    // ✅ تحقق من الرقم الضريبي (15 رقم بالضبط)
    if (!preg_match('/^\d{15}$/', $tax_number)) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ الرقم الضريبي يجب أن يكون 15 رقم بالضبط.'];
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }

    // ✅ تحقق من التكرار
    $checkTax = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE bill_number = ?");
    $checkTax->execute([$bill_number]);
    if ($checkTax->fetchColumn() > 0) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ رقم الفاتورة مستخدم بالفعل.'];
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }

    // رفع صورة الفاتورة العامة
    $invoiceImage = upload_image('invoice_image');

    if ($xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
        $rows = $xlsx->rows();
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        $required = ['name','unit_type','unit','quantity','price','unit_quantity'];
        foreach($required as $col){
            if(!in_array($col,$header)){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ الملف لا يحتوي على العمود: $col"];
                header('Location: ' . BASE_URL . '/purchases.php');
                exit;
            }
        }

        // حساب الإجمالي
        $total = 0;
        $items = [];
        foreach ($rows as $r) {
            $data = array_combine($header, $r);
            $q = (float)($data['quantity'] ?? 0);
            $p = (float)($data['price'] ?? 0);
            $total += $q * $p;
            $items[] = $data;
        }

        $vat = round($total * 0.15, 2);
        $all_total = $total + $vat;
        $created_at = $invoice_date . ' ' . date('H:i:s');

        // رقم الفاتورة العشوائي الفريد
        do {
            $invoice_number = rand(100000, 999999);
            $checkInvoice = $pdo->prepare("SELECT COUNT(*) FROM orders_purchases WHERE invoice_number=?");
            $checkInvoice->execute([$invoice_number]);
        } while ($checkInvoice->fetchColumn() > 0);

        // إنشاء رقم تسلسلي بصيغة DAELP00001
        $lastSerial = $pdo->query("SELECT invoice_serial FROM orders_purchases ORDER BY id DESC LIMIT 1")->fetchColumn();
        if ($lastSerial && preg_match('/DAELP(\d+)/', $lastSerial, $m)) {
            $nextNumber = (int)$m[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $serial_invoice = "DAELP" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

        // إنشاء الفاتورة الرئيسية
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders_purchases (bill_number, tax_number, invoice_number, invoice_serial, supplier_name, total, vat, all_total, created_at, invoice_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtOrder->execute([$bill_number, $tax_number, $invoice_number, $serial_invoice, $supplier_name, $total, $vat, $all_total, $created_at, $invoiceImage]);
        $order_id = $pdo->lastInsertId();

        // تحضير استعلام الأصناف
        $stmt = $pdo->prepare("
            INSERT INTO purchases (name, quantity, prinitng_quantity , single_package , total_packages, unit, package, price , total_price, payer_name, payment_source ,unit_total ,unit_vat ,unit_all_total, order_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?, ? ,? ,?, ?)
        ");

        foreach($items as $data) {
            $name = trim($data['name']);
            if (!$name) continue;

            $unit = $data['unit_type'];
            $package = trim($data['unit'] ?? '');
            $payer = $payer_name;
            $source = $payment_source;
            $quantity = (float)$data['quantity'];
            $price = (float)$data['price'];
            $unit_quantity = (float)$data['unit_quantity'];

            $single_quantity = $quantity * $unit_quantity;
            $unit_price = $price / $unit_quantity;


            $vatRate = 0.15;
            $subtotal_unit = $quantity * $price;
            $vat_unit = $subtotal_unit * $vatRate;
            $alltotal_unit = $subtotal_unit + $vat_unit;

            $stmt->execute([$name, $single_quantity, $single_quantity ,$unit_quantity , $quantity , $unit, $package, $unit_price , $price, $payer, $source, $subtotal_unit , $vat_unit , $alltotal_unit, $order_id]);
            $purchase_id = $pdo->lastInsertId();

            // ✅ تطبيق منطق العهدة (نفس كود الإضافة اليدوية)
            if ($source === 'عهدة') {
                $amountNeeded = ($price * $quantity) + $vat_unit;
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$payer]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                foreach ($custodies as $custody) {
                    if ($amountNeeded <= 0) break;

                    if ($custody['amount'] >= $amountNeeded) {
                        $newAmount = $custody['amount'] - $amountNeeded;
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                        $stmtTx = $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmtTx->execute(['purchase', $purchase_id, $custody['id'], $amountNeeded]);

                        $amountNeeded = 0;
                    } else {
                        $amountToUse = $custody['amount'];
                        $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);

                        $stmtTx = $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmtTx->execute(['purchase', $purchase_id, $custody['id'], $amountToUse]);

                        $amountNeeded -= $amountToUse;
                    }
                }

                if ($amountNeeded > 0) {
                    $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كاف للشخص: ' . htmlspecialchars($payer)];
                    header('Location: ' . BASE_URL . '/purchases.php');
                    exit;
                }
            }
        }

        $_SESSION['toast'] = ['type'=>'success','msg'=>"✅ تم استيراد الأصناف وإنشاء الفاتورة رقم {$serial_invoice} بنجاح"];
    } else {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ خطأ في قراءة الملف: ".\Shuchkin\SimpleXLSX::parseError()];
    }

} else {
    $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ طلب غير صالح'];
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;

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
