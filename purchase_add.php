<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $name = trim($_POST['name']);
    $quantity = (float)($_POST['quantity'] ?? 0);
    $unit = $_POST['unit'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $payer = trim($_POST['payer_name'] ?? '');
    $payment_source = $_POST['payment_source'] ?? '';
    $total = $quantity*$price;

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE name=? AND unit=? AND payer_name=?");
    $check->execute([$name,$unit,$payer]);
    if($check->fetchColumn()>0){
        $_SESSION['toast']=['type'=>'warning','msg'=>'هناك عملية شراء بنفس الاسم، الوحدة والدافع موجودة بالفعل'];
        header('Location: purchases.php'); exit;
    }

    // التعامل مع العهدة
    $deducted = 0;
    if($payment_source==='custody'){
        $stmt = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount>0 ORDER BY amount DESC LIMIT 1");
        $stmt->execute([$payer]); $custody = $stmt->fetch(PDO::FETCH_ASSOC);
        if($custody){
            $deducted = min($custody['amount'],$total);
            $pdo->prepare("UPDATE custodies SET amount = amount - ? WHERE person_name=?")->execute([$deducted,$payer]);
        }
    }
    $remaining = $total - $deducted;

    $stmt = $pdo->prepare("INSERT INTO purchases (name, quantity, unit, price, product_image, invoice_image, payer_name, payment_source) VALUES (?,?,?,?,?,?,?,?)");
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

    $_SESSION['toast']=['type'=>'success','msg'=>'تمت العملية بنجاح'];
}
header('Location: purchases.php'); exit;
