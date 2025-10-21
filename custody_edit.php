<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id'];
    $o = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
    $o->execute([$id]);
    $old = $o->fetch(PDO::FETCH_ASSOC);

    $person_name = trim($_POST['person_name'] ?? '');
    $amount      = (float)($_POST['amount'] ?? 0);
    $taken_at    = trim($_POST['taken_at'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');

    $newData = [
        'person_name' => $person_name,
        'amount'      => $amount,
        'taken_at'    => $taken_at,
        'notes'       => $notes
    ];

    $changed = false;
    foreach ($newData as $k => $v) {
        if (!isset($old[$k]) || $old[$k] != $v) { 
            $changed = true; 
            break; 
        }
    }

    if($changed){
        $stmt = $pdo->prepare("UPDATE custodies SET person_name=?, amount=?,main_amount=?,sub_amount=?, taken_at=?, notes=? WHERE id=?");
        $stmt->execute([$person_name,$amount,$amount,$amount,$taken_at,$notes,$id]);
        $_SESSION['toast'] = ['type'=>'success','msg'=>'تم التعديل بنجاح'];
    } else {
        $_SESSION['toast'] = ['type'=>'info','msg'=>'لا تغييرات للحفظ'];
    }
}

header('Location: '.BASE_URL.'/custodies.php');
exit;
