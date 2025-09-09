<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $person_name = trim($_POST['person_name']);
    $amount      = (float)($_POST['amount'] ?? 0);
    $taken_at    = trim($_POST['taken_at']);
    $notes       = trim($_POST['notes'] ?? '');

    $pdo->prepare("INSERT INTO custodies(person_name,amount,taken_at,notes) VALUES(?,?,?,?)")
        ->execute([$person_name,$amount,$taken_at,$notes]);

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت الإضافة بنجاح'];
}

header('Location: '.BASE_URL.'/custodies.php');
exit;
