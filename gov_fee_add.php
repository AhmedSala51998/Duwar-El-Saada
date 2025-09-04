<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $fee_title = trim($_POST['fee_title']);
    $fee_type  = trim($_POST['fee_type']);
    $payer     = $_POST['payer'];

    // تحقق من التكرار
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gov_fees WHERE fee_title=? AND fee_type=? AND payer=?");
    $stmt->execute([$fee_title, $fee_type, $payer]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك رسم حكومي بنفس العنوان والنوع والدافع موجود بالفعل'
        ];
    } else {
        $stmt = $pdo->prepare("INSERT INTO gov_fees(fee_title,fee_type,fee_amount,payer,invoice_image) VALUES(?,?,?,?,?)");
        $stmt->execute([
            $fee_title,
            $fee_type,
            (float)($_POST['fee_amount'] ?? 0),
            $payer,
            upload_image('invoice_image')
        ]);
        $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
    }
}

header('Location: gov_fees.php');
exit;
