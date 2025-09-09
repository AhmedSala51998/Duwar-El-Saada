<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM assets WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $newData = [
        'name'          => trim($_POST['name']),
        'type'          => $_POST['type'] ?? '',
        'quantity'      => (float)($_POST['quantity'] ?? 0),
        'price'         => (float)($_POST['price'] ?? 0),
        'image'         => upload_image('image') ?: ($oldData['image'] ?? null),
        'payer_name'    => trim($_POST['payer_name'] ?? ''),
        'payment_source'=> $_POST['payment_source'] ?? 'كاش'
    ];

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name=? AND type=? AND payer_name=? AND id<>?");
    $check->execute([$newData['name'], $newData['type'], $newData['payer_name'], $id]);
    $exists = $check->fetchColumn();

    if($exists > 0){
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'هناك أصل بنفس الاسم، النوع والدافع موجود بالفعل'];
    } else {

        // تحقق إذا كان هناك أي تغيير فعلي
        $changed = false;
        foreach(['name','type','quantity','price','image','payer_name','payment_source'] as $key){
            if(!isset($oldData[$key]) || $oldData[$key] != $newData[$key]){
                $changed = true;
                break;
            }
        }

        if($changed){

            // استرجاع العهدة القديمة إذا كانت مدفوعة من العهدة
            if($oldData['payment_source'] === 'عهدة'){
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
                $stmtC->execute([$oldData['payer_name']]);
                $custody = $stmtC->fetch();
                if($custody){
                    $newAmount = $custody['amount'] + $oldData['price'];
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                }
            }

            // خصم العهدة الجديدة إذا مصدر الدفع "عهدة"
            if($newData['payment_source'] === 'عهدة'){
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
                $stmtC->execute([$newData['payer_name']]);
                $custody = $stmtC->fetch();
                if($custody && $custody['amount'] >= $newData['price']){
                    $newAmount = $custody['amount'] - $newData['price'];
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                } else {
                    $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                    header('Location: ' . BASE_URL . '/assetes.php'); exit;
                }
            }

            // التحديث في قاعدة البيانات
            $pdo->prepare("UPDATE assets SET name=?, type=?, quantity=?, price=?, payer_name=?, payment_source=?, image=? WHERE id=?")
                ->execute([$newData['name'], $newData['type'], $newData['quantity'], $newData['price'], $newData['payer_name'], $newData['payment_source'], $newData['image'], $id]);

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل الأصل بنجاح'];

        } else {
            // لا تغييرات → رسالة معلوماتية
            $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
        }
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
