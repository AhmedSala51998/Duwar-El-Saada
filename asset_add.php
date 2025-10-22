<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

    $bill_number = trim($_POST['bill_number'] ?? '');
    if ($bill_number !== '') {
        // فحص التكرار
        $check = $pdo->prepare("SELECT id FROM assets WHERE bill_number = ?");
        $check->execute([$bill_number]);
        if ($check->fetch()) {
            $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'رقم فاتورة المورد مكرر بالفعل'];
            header('Location: ' . BASE_URL . '/assetes.php');
            exit;
        }
    }

    $name = trim($_POST['name']);
    $type = $_POST['type'] ?? '';
    $payer = trim($_POST['payer_name'] ?? '');
    $payment_source = $_POST['payment_source'] ?? 'كاش';
    $quantity = (float)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $has_vat = isset($_POST['has_vat']) ? (int)$_POST['has_vat'] : 0;
    $vat_value = 0;
    $total_amount = $price * $quantity;

    $lastSerial = $pdo->query("SELECT invoice_serial FROM assets ORDER BY id DESC LIMIT 1")->fetchColumn();
    $nextNumber = 1;
    if ($lastSerial && preg_match('/DAELA(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    }
    $serial_invoice = "DAELA" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    if ($has_vat) {
        $vat_value = $total_amount * 0.15;
        $total_amount += $vat_value;
    }

    $image = upload_image('image');

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name=? AND type=? AND payer_name=?");
    $check->execute([$name, $type, $payer]);
    $exists = $check->fetchColumn();
    if ($exists > 0) {
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'هناك أصل بنفس الاسم، النوع والدافع موجود بالفعل'];
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

    try {
        // بدء Transaction
        $pdo->beginTransaction();

        // إدخال الأصل
        $pdo->prepare("INSERT INTO assets (bill_number , invoice_serial, name, type, quantity, price, has_vat, vat_value, total_amount, payer_name, payment_source, image) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $bill_number,
                $serial_invoice,
                $name,
                $type,
                $quantity,
                $price,
                $has_vat,
                $vat_value,
                $total_amount,
                $payer,
                $payment_source,
                $image
            ]);

        $asset_id = $pdo->lastInsertId();

        // خصم العهدة إذا مصدر الدفع "عهدة"
        if($payment_source === 'عهدة'){
            $amountToDeduct = ($price * $quantity) + $vat_value;

            // جلب كل العهد للشخص، الأقدم أولاً
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
            $stmtC->execute([$payer]);
            $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

            $totalAvailable = array_sum(array_column($custodies, 'amount'));
            if($totalAvailable < $amountToDeduct){
                throw new Exception('رصيد العهدة غير كافي');
            }

            foreach ($custodies as $custody) {
                if ($amountToDeduct <= 0) break;

                $notes = "شراء " . $name;

                if ($custody['amount'] >= $amountToDeduct) {
                    $newAmount = $custody['amount'] - $amountToDeduct;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                    $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
                        ->execute(['asset', $asset_id, $custody['id'], $amountToDeduct, $notes]);
                    $amountToDeduct = 0;
                } else {
                    $amountDeducted = $custody['amount'];
                    $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                    $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
                        ->execute(['asset', $asset_id, $custody['id'], $amountDeducted, $notes]);
                    $amountToDeduct -= $amountDeducted;
                }
            }
        }

        $pdo->commit(); // تأكيد Transaction
        $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];

    } catch (Exception $e) {
        $pdo->rollBack(); // التراجع عن كل العمليات في حالة الخطأ
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'فشل العملية: ' . $e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
