<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $name = trim($_POST['name']);
    $unit = $_POST['unit'];
    $payer = trim($_POST['payer_name'] ?? '');

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
        $stmt = $pdo->prepare("
            INSERT INTO purchases (name, quantity, unit, price, product_image, invoice_image, payer_name) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            (float)($_POST['quantity'] ?? 0),
            $unit,
            (float)($_POST['price'] ?? 0),
            upload_image('product_image'),
            upload_image('invoice_image'),
            $payer
        ]);

        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تمت العملية بنجاح'
        ];
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
