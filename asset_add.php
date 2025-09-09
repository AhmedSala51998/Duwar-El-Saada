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
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
            $stmtC->execute([$payer]);
            $custody = $stmtC->fetch();
            if($custody && $custody['amount'] >= $price){
                $newAmount = $custody['amount'] - $price;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            } else {
                $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                header('Location: ' . BASE_URL . '/assetes.php'); exit;
            }
        }

        $pdo->prepare("INSERT INTO assets (name, type, quantity, price, payer_name, payment_source, image) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$name, $type, $quantity, $price, $payer, $payment_source, $image]);

        $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
