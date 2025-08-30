<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $stmt = $pdo->prepare("
        INSERT INTO purchases (name, quantity, unit, price, product_image, invoice_image, payer_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        trim($_POST['name']),
        (float)$_POST['quantity'],
        $_POST['unit'],
        (float)($_POST['price'] ?? 0),
        upload_image('product_image'),
        upload_image('invoice_image'),
        trim($_POST['payer_name'] ?? '')
    ]);

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تمت العملية بنجاح'
    ];
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
