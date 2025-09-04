<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $service_name = trim($_POST['service_name']);
    $subscribers = trim($_POST['subscribers']);
    $subscription_type = $_POST['subscription_type'] ?? '';
    $payer = $_POST['payer'] ?? '';

    // التحقق من وجود سجل مطابق مسبقًا
    $check = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE service_name=? AND subscribers=? AND subscription_type=? AND payer=?");
    $check->execute([$service_name, $subscribers, $subscription_type, $payer]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هذه الاشتراكات موجودة بالفعل بنفس المواصفات'
        ];
    } else {
        $stmt = $pdo->prepare("INSERT INTO subscriptions(service_name,subscribers,subscription_type,service_price,payer,invoice_image) VALUES(?,?,?,?,?,?)");
        $stmt->execute([
            $service_name,
            $subscribers,
            $subscription_type,
            (float)($_POST['service_price'] ?? 0),
            $payer,
            upload_image('invoice_image')
        ]);

        $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
    }
}

header('Location: subscriptions.php');
exit;
