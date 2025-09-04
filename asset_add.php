<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $name = trim($_POST['name']);
    $type = trim($_POST['type'] ?? '');

    // تحقق من وجود أصل بنفس الاسم والنوع
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name=? AND type=?");
    $stmt->execute([$name, $type]);
    $exists = $stmt->fetchColumn();

    if($exists > 0){
        // إذا موجود بالفعل
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك أصل بنفس الاسم والنوع موجود بالفعل'
        ];
    } else {
        // إذا غير موجود، إضافة الأصل
        $pdo->prepare("INSERT INTO assets(name,type,quantity,price,payer_name,image) VALUES(?,?,?,?,?,?)")
            ->execute([
                $name,
                $type,
                (int)($_POST['quantity'] ?? 1),
                (float)($_POST['price'] ?? 0),
                trim($_POST['payer_name'] ?? ''),
                upload_image('image')
            ]);
        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تمت العملية بنجاح'
        ];
    }
}

header('Location: '.BASE_URL.'/assetes.php');
exit;
