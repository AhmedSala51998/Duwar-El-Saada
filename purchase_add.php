<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $name = trim($_POST['name']);
    $unit = $_POST['unit'];
    $payer = trim($_POST['payer_name'] ?? '');
    $payment_source = $_POST['payment_source'] ?? 'كاش';
    $quantity = (float)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE name=? AND unit=? AND payer_name=?");
    $check->execute([$name, $unit, $payer]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك عملية شراء بنفس الاسم، الوحدة والدافع موجودة بالفعل'
        ];
    } else {

        // خصم العهدة إذا مصدر الدفع "عهدة"
        if($payment_source === 'عهدة'){
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
            $stmtC->execute([$payer]);
            $custody = $stmtC->fetch();
            if($custody && $custody['amount'] >= $price){
                $newAmount = $custody['amount'] - $price;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            } else {
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => 'رصيد العهدة غير كافي'
                ];
                header('Location: ' . BASE_URL . '/purchases.php'); exit;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO purchases (name, quantity, unit, price, product_image, invoice_image, payer_name, payment_source) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $quantity,
            $unit,
            $price,
            upload_image('product_image'),
            upload_image('invoice_image'),
            $payer,
            $payment_source
        ]);

        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تمت العملية بنجاح'
        ];
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
