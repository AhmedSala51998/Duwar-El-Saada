<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $rental_name = trim($_POST['rental_name']);
    $payment_type = $_POST['payment_type'];
    $rental_kind = trim($_POST['rental_kind'] ?? '');
    $payer = $_POST['payer'];

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE rental_name=? AND payment_type=? AND rental_kind=? AND payer=?");
    $check->execute([$rental_name, $payment_type, $rental_kind, $payer]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هذا الإيجار موجود بالفعل بنفس الاسم، نوع الدفع، نوع الإيجار والدافع'
        ];
    } else {
        $stmt = $pdo->prepare("INSERT INTO rentals(rental_name,payment_type,rental_price,rental_kind,payer,invoice_image) VALUES(?,?,?,?,?,?)");
        $stmt->execute([
            $rental_name,
            $payment_type,
            (float)($_POST['rental_price'] ?? 0),
            $rental_kind,
            $payer,
            upload_image('invoice_image')
        ]);

        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تمت العملية بنجاح'
        ];
    }
}

header('Location: rentals.php');
exit;
