<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config/config.php';
require_permission('assets.addAssetExcel');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ لم يتم رفع ملف Excel بشكل صحيح'];
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

    require_once __DIR__ . '/libs/SimpleXLSX.php';
    $filePath = $_FILES['excel_file']['tmp_name'];

    $payer_name      = trim($_POST['payer_name'] ?? '');
    $payment_source  = trim($_POST['payment_source'] ?? 'كاش');

    // التحقق من تكرار رقم الفاتورة
    $check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE bill_number=?");
    $check->execute([$bill_number]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ رقم الفاتورة مستخدم بالفعل.'];
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

    $invoiceImage = upload_image('invoice_image');

    if ($xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
        $rows = $xlsx->rows();
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        // الأعمدة المطلوبة
        $required = ['name','type','quantity','price','has_vat'];
        foreach($required as $col){
            if(!in_array($col,$header)){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ الملف لا يحتوي على العمود: $col"];
                header('Location: ' . BASE_URL . '/assetes.php');
                exit;
            }
        }

        // توليد رقم تسلسلي للفاتورة
        $lastSerial = $pdo->query("SELECT invoice_serial FROM assets ORDER BY id DESC LIMIT 1")->fetchColumn();
        if ($lastSerial && preg_match('/DAELA(\d+)/', $lastSerial, $m)) {
            $nextNumber = (int)$m[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $serial_invoice = "DAELA" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO assets (bill_number, invoice_serial, name, type, quantity, price, has_vat, vat_value, total_amount, payer_name, payment_source, image, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($rows as $r) {
                $data = array_combine($header, $r);
                $name = trim($data['name']);
                if (!$name) continue;

                $bill_number     = trim($data['invoice_serial'] ?? '');
                $invoice_date    = trim($data['invoice_date'] ?? date('Y-m-d'));
                $type = trim($data['type']);
                $quantity = (float)($data['quantity'] ?? 0);
                $price = (float)($data['price'] ?? 0);
                $has_vat = (int)($data['has_vat'] ?? 0);

                $total = $quantity * $price;
                $vat_value = $has_vat ? $total * 0.15 : 0;
                $total_amount = $total + $vat_value;

                $stmt->execute([
                    $bill_number,
                    $serial_invoice,
                    $name,
                    $type,
                    $quantity,
                    $price,
                    $has_vat,
                    $vat_value,
                    $total_amount,
                    $payer_name,
                    $payment_source,
                    $invoiceImage,
                    $invoice_date
                ]);

                $asset_id = $pdo->lastInsertId();

                // التعامل مع العهدة إن وجدت
                if ($payment_source === 'عهدة') {
                    $amountToDeduct = $total_amount;
                    $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                    $stmtC->execute([$payer_name]);
                    $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                    $totalAvailable = array_sum(array_column($custodies, 'amount'));
                    if($totalAvailable < $amountToDeduct){
                        $pdo->rollBack();
                        $_SESSION['toast'] = [
                            'type' => 'danger',
                            'msg'  => 'رصيد العهدة غير كافٍ للشخص: ' . htmlspecialchars($payer_name)
                        ];
                        header('Location: ' . BASE_URL . '/assetes.php');
                        exit;
                    }

                    foreach ($custodies as $custody) {
                        if ($amountToDeduct <= 0) break;
                        $notes = "شراء " . $name;

                        if ($custody['amount'] >= $amountToDeduct) {
                            $newAmount = $custody['amount'] - $amountToDeduct;
                            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                            $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at)
                                           VALUES ('asset', ?, ?, ?, ?, NOW())")
                                ->execute([$asset_id, $custody['id'], $amountToDeduct, $notes]);
                            $amountToDeduct = 0;
                        } else {
                            $amountDeducted = $custody['amount'];
                            $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                            $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at)
                                           VALUES ('asset', ?, ?, ?, ?, NOW())")
                                ->execute([$asset_id, $custody['id'], $amountDeducted, $notes]);
                            $amountToDeduct -= $amountDeducted;
                        }
                    }
                }
            }

            $pdo->commit();
            $_SESSION['toast'] = ['type'=>'success','msg'=>"✅ تم استيراد الأصول وإنشاء الفاتورة رقم {$serial_invoice} بنجاح"];

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ فشل العملية: " . $e->getMessage()];
        }

    } else {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ خطأ في قراءة الملف: ".\Shuchkin\SimpleXLSX::parseError()];
    }

} else {
    $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ طلب غير صالح'];
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;


// دالة رفع الصورة
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
