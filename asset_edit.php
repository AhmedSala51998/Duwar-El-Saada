<?php require __DIR__.'/config/config.php'; require_role(['admin','manager']);
if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
  $id=(int)$_POST['id']; $o=$pdo->prepare("SELECT image FROM assets WHERE id=?"); $o->execute([$id]); $old=$o->fetch();
  $img = upload_image('image') ?: ($old['image']??null);
  $pdo->prepare("UPDATE assets SET name=?, price=?, payer_name=?, image=? WHERE id=?")
      ->execute([trim($_POST['name']),(float)($_POST['price']??0),trim($_POST['payer_name']??''),$img,$id]);
      $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تمت العملية بنجاح'
      ];
}
header('Location: '.BASE_URL.'/assets.php');