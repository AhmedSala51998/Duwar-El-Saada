<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $newData = [
        'name'          => trim($_POST['name']),
        'quantity'      => (float)($_POST['quantity'] ?? 0),
        'unit'          => $_POST['unit'] ?? '',
        'price'         => (float)($_POST['price'] ?? 0),
        'product_image' => upload_image('product_image') ?: ($oldData['product_image'] ?? null),
        'invoice_image' => upload_image('invoice_image') ?: ($oldData['invoice_image'] ?? null),
        'payer_name'    => trim($_POST['payer_name'] ?? ''),
        'payment_source'=> $_POST['payment_source'] ?? 'كاش'
    ];

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE name=? AND unit=? AND payer_name=? AND id<>?");
    $check->execute([$newData['name'], $newData['unit'], $newData['payer_name'], $id]);
    $exists = $check->fetchColumn();

    if($exists > 0){
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'هناك عملية شراء بنفس الاسم، الوحدة والدافع موجودة بالفعل'];
    } else {

        // التحقق إذا كان هناك أي تغيير فعلي
        $changed = false;
        foreach(['name','quantity','unit','price','product_image','invoice_image','payer_name','payment_source'] as $key){
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
                    header('Location: ' . BASE_URL . '/purchases.php'); exit;
                }
            }

            // التحديث في قاعدة البيانات
            $pdo->prepare("
                UPDATE purchases SET name=?, quantity=?, unit=?, price=?, product_image=?, invoice_image=?, payer_name=?, payment_source=? 
                WHERE id=?
            ")->execute([
                $newData['name'],
                $newData['quantity'],
                $newData['unit'],
                $newData['price'],
                $newData['product_image'],
                $newData['invoice_image'],
                $newData['payer_name'],
                $newData['payment_source'],
                $id
            ]);

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل العملية بنجاح'];

        } else {
            $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
        }
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
