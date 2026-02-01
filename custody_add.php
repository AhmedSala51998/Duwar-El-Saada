<?php
require __DIR__.'/config/config.php';
require_permission('custodies.add');

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $person_name = trim($_POST['person_name']);
    $branch_id   = (int)($_POST['branch_id'] ?? 0);
    $amount      = (float)($_POST['amount'] ?? 0);
    $main_amount      = (float)($_POST['amount'] ?? 0);
    $taken_at    = trim($_POST['taken_at']);
    $notes       = trim($_POST['notes'] ?? '');

    if (!$branch_id) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ يرجى اختيار الفرع'];
        header('Location: '.BASE_URL.'/custodies.php');
        exit;
    }

    $lastSerial = $pdo->query("SELECT invoice_serial FROM custodies ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($lastSerial && preg_match('/DAELC(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    } else {
        $nextNumber = 1;
    }
    $serial_invoice = "DAELC" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    $pdo->prepare("INSERT INTO custodies(branch_id, invoice_serial , person_name,amount , main_amount,sub_amount,taken_at,notes) VALUES(?,?,?,?,?,?,?,?)")
        ->execute([$branch_id, $serial_invoice,$person_name,$amount , $main_amount,$main_amount,$taken_at,$notes]);

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت الإضافة بنجاح'];
}

header('Location: '.BASE_URL.'/custodies.php');
exit;
