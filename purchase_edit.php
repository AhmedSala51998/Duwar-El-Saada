<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    $old = $pdo->prepare("SELECT product_image, invoice_image FROM purchases WHERE id=?");
    $old->execute([$id]);
    $o = $old->fetch();

    $pimg = upload_image('product_image') ?: ($o['product_image'] ?? null);
    $iimg = upload_image('invoice_image') ?: ($o['invoice_image'] ?? null);

    $pdo->prepare("
        UPDATE purchases 
        SET name=?, quantity=?, unit=?, price=?, product_image=?, invoice_image=?, payer_name=? 
        WHERE id=?
    ")->execute([
        trim($_POST['name']),
        (float)$_POST['quantity'],
        $_POST['unit'],
        (float)($_POST['price'] ?? 0),
        $pimg,
        $iimg,
        trim($_POST['payer_name'] ?? ''),
        $id
    ]);

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تم تعديل العملية بنجاح'
    ];
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
