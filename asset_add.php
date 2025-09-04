<?php require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
  $pdo->prepare("INSERT INTO assets(name,type,quantity,price,payer_name,image) VALUES(?,?,?,?,?,?)")
      ->execute([
        trim($_POST['name']),
        trim($_POST['type'] ?? ''),
        (int)($_POST['quantity'] ?? 1),
        (float)($_POST['price']??0),
        trim($_POST['payer_name']??''),
        upload_image('image')
      ]);
  $_SESSION['toast'] = [
    'type' => 'success',
    'msg'  => 'تمت العملية بنجاح'
  ];
}
header('Location: '.BASE_URL.'/assetes.php');
