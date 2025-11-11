<?php
require __DIR__ . '/config/config.php';
require_permission('assets.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

    $payer_name     = trim($_POST['payer_name'] ?? '');
    $payment_source = trim($_POST['payment_source'] ?? 'كاش');

    // فحص تكرار رقم الفاتورة
    if ($invoice_serial !== '') {
        $check = $pdo->prepare("SELECT id FROM assets WHERE invoice_serial = ?");
        $check->execute([$invoice_serial]);
        if ($check->fetch()) {
            $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'رقم الفاتورة مكرر بالفعل'];
            header('Location: ' . BASE_URL . '/assetes.php');
            exit;
        }
    }

    // رفع صورة الفاتورة
    $invoice_image = upload_image('invoice_image');

    // جلب آخر تسلسل فاتورة
    $lastSerial = $pdo->query("SELECT invoice_serial FROM assets ORDER BY id DESC LIMIT 1")->fetchColumn();
    $nextNumber = 1;
    if ($lastSerial && preg_match('/DAELA(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    }
    $serial_invoice = "DAELA" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    // التأكد من وجود بيانات أصول
    if (empty($_POST['name']) || !is_array($_POST['name'])) {
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'لم يتم إدخال أي أصول'];
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO assets 
            (bill_number, invoice_serial, name, type, quantity, price, has_vat, vat_value, total_amount, payer_name, payment_source, image, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($_POST['name'] as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;

            $invoice_serial = trim($_POST['invoice_serial'][$i] ?? '');
            $invoice_date   = trim($_POST['invoice_date'][$i] ?? date('Y-m-d'));
            $type = trim($_POST['type'][$i] ?? '');
            $quantity = (float)($_POST['quantity'][$i] ?? 0);
            $price = (float)($_POST['price'][$i] ?? 0);
            $has_vat = (int)($_POST['has_vat'][$i] ?? 0);

            $total = $quantity * $price;
            $vat_value = $has_vat ? $total * 0.15 : 0;
            $total_amount = $total + $vat_value;

            $stmt->execute([
                $invoice_serial,   // bill_number (نفس الرقم للفاتورة كلها)
                $serial_invoice,   // الرقم التسلسلي العام
                $name,
                $type,
                $quantity,
                $price,
                $has_vat,
                $vat_value,
                $total_amount,
                $payer_name,
                $payment_source,
                $invoice_image,
                $invoice_date
            ]);

            $asset_id = $pdo->lastInsertId();

            // التعامل مع العهدة
            if ($payment_source === 'عهدة') {
                $amountToDeduct = $total_amount;
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$payer_name]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                $totalAvailable = array_sum(array_column($custodies, 'amount'));
                if ($totalAvailable < $amountToDeduct) {
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
        $_SESSION['toast'] = ['type' => 'success', 'msg' => '✅ تم حفظ الأصول بنجاح'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => '❌ فشل العملية: ' . $e->getMessage()];
    }
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
