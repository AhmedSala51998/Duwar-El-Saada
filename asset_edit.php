<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM assets WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $quantity = (float)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $total_price = $price * $quantity; // السعر × الكمية

    $has_vat = isset($_POST['has_vat']) ? (int)$_POST['has_vat'] : 0;
    $vat_value = $has_vat ? $total_price * 0.15 : 0;
    $total_amount = $total_price + $vat_value;


    $newData = [
        'name' => trim($_POST['name']),
        'type' => $_POST['type'] ?? '',
        'quantity' => (float)($_POST['quantity'] ?? 0),
        'price' => $price,
        'has_vat' => $has_vat,
        'vat_value' => $vat_value,
        'total_amount' => $total_amount,
        'image' => upload_image('image') ?: ($oldData['image'] ?? null),
        'payer_name' => trim($_POST['payer_name'] ?? ''),
        'payment_source' => $_POST['payment_source'] ?? 'كاش'
    ];

    // تحقق من التكرار
    $check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name=? AND type=? AND payer_name=? AND id<>?");
    $check->execute([$newData['name'], $newData['type'], $newData['payer_name'], $id]);
    $exists = $check->fetchColumn();

    if($exists > 0){
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'هناك أصل بنفس الاسم، النوع والدافع موجود بالفعل'];
    } else {
        $changed = false;
        foreach(['name','type','quantity','price','has_vat','vat_value','total_amount','image','payer_name','payment_source'] as $key){
            if(!isset($oldData[$key]) || $oldData[$key] != $newData[$key]){
                $changed = true;
                break;
            }
        }

        if($changed){
            // استرجاع العهدة القديمة إذا كانت مدفوعة من العهدة
            // إعادة العهدة القديمة
            if($oldData['payment_source'] === 'عهدة'){
                $amountToReturn = $oldData['price'] * $oldData['quantity'];
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
                $stmtC->execute([$oldData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                foreach($custodies as $custody){
                    if($amountToReturn <= 0) break;

                    $add = $amountToReturn; // نعيد كامل المبلغ المتبقي
                    $newAmount = $custody['amount'] + $add;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                    // سجل عملية الإرجاع
                    $stmtTx = $pdo->prepare("
                        INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmtTx->execute(['refund', $oldData['id'], $custody['id'], $add]);

                    $amountToReturn -= $add;
                }
            }

            // خصم العهدة الجديدة
            if($newData['payment_source'] === 'عهدة'){
                $amountToDeduct = $price * $quantity;
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$newData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                foreach($custodies as $custody){
                    if($amountToDeduct <= 0) break;

                    $deduct = min($custody['amount'], $amountToDeduct);
                    $newAmount = $custody['amount'] - $deduct;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                    // سجل عملية الخصم
                    $stmtTx = $pdo->prepare("
                        INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmtTx->execute(['deduct', $oldData['id'], $custody['id'], $deduct]);

                    $amountToDeduct -= $deduct;
                }

                if($amountToDeduct > 0){
                    $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                    header('Location: ' . BASE_URL . '/assetes.php'); 
                    exit;
                }
            }


            // تحديث قاعدة البيانات
            $pdo->prepare("UPDATE assets SET name=?, type=?, quantity=?, price=?, has_vat=?, vat_value=?, total_amount=?, payer_name=?, payment_source=?, image=? WHERE id=?")
                ->execute([$newData['name'],$newData['type'],$newData['quantity'],$price,$has_vat,$vat_value,$total_amount,$newData['payer_name'],$newData['payment_source'],$newData['image'],$id]);

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل الأصل بنجاح'];
        } else {
            $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
        }
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
