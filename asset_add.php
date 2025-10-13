<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

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
    if ($lastSerial && preg_match('/DAELA(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    } else {
        $nextNumber = 1;
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
    } else {

        // خصم العهدة إذا مصدر الدفع "عهدة"
        if($payment_source === 'عهدة'){
            $amountToDeduct = $price * $quantity;

            // جلب كل العهد للشخص، الأقدم أولاً
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
            $stmtC->execute([$payer]);
            $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

            $totalAvailable = array_sum(array_column($custodies, 'amount'));
            if($totalAvailable < $amountToDeduct){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                header('Location: ' . BASE_URL . '/assetes.php'); 
                exit;
            }

            foreach($custodies as $custody){
                if($amountToDeduct <= 0) break;

                $deduct = min($custody['amount'], $amountToDeduct);
                $newAmount = $custody['amount'] - $deduct;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                $amountToDeduct -= $deduct;
            }
        }


        $pdo->prepare("INSERT INTO assets (invoice_serial, name, type, quantity, price, has_vat, vat_value, total_amount, payer_name, payment_source, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
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

        $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
