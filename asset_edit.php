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

    // ✅ لو الكمية أصبحت صفر نحذف الأصل تمامًا ونرجع العهدة لو كانت عهدة
    if ($quantity == 0) {

        // استرجاع العهدة إذا كانت مدفوعة من العهدة
        if ($oldData['payment_source'] === 'عهدة') {
            $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type='asset' AND type_id=?");
            $stmtTx->execute([$oldData['id']]);
            $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

            foreach ($transactions as $tx) {
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
                $stmtC->execute([$tx['custody_id']]);
                $custody = $stmtC->fetch();

                if ($custody) {
                    $newAmount = $custody['amount'] + $tx['amount'];
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                }
            }

            // حذف معاملات العهدة بعد الإرجاع
            $pdo->prepare("DELETE FROM custody_transactions WHERE type='asset' AND type_id=?")->execute([$oldData['id']]);
        }

        // حذف الأصل نفسه
        $pdo->prepare("DELETE FROM assets WHERE id=?")->execute([$id]);

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم حذف الأصل لأن الكمية أصبحت صفر'];
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }

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
            // إعادة العهدة القديمة إذا كان مصدر الدفع عهدة
            if ($oldData['payment_source'] === 'عهدة') {
                $amountToReturn = $oldData['price'] * $oldData['quantity'];

                // جلب كل المعاملات السابقة الخاصة بهذا الصنف
                $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type='asset' AND type_id=?");
                $stmtTx->execute([$oldData['id']]);
                $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

                foreach ($transactions as $tx) {
                    // جلب العهدة الأصلية
                    $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
                    $stmtC->execute([$tx['custody_id']]);
                    $custody = $stmtC->fetch();

                    if ($custody) {
                        $newAmount = $custody['amount'] + $tx['amount'];
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                    }
                }

                // حذف المعاملات بعد الإرجاع
                $pdo->prepare("DELETE FROM custody_transactions WHERE type='asset' AND type_id=?")->execute([$oldData['id']]);
            }

            // خصم العهدة الجديدة إذا كان مصدر الدفع عهدة
            if ($newData['payment_source'] === 'عهدة') {
                $amountNeeded = $price * $quantity;

                // جلب كل العهد المتاحة للشخص بالترتيب من الأقدم للأحدث
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$newData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                foreach ($custodies as $custody) {
                    if ($amountNeeded <= 0) break;

                    $deduct = min($custody['amount'], $amountNeeded);
                    $newAmount = $custody['amount'] - $deduct;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                    // سجل المعاملة
                    $stmtTx = $pdo->prepare("
                        INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmtTx->execute(['asset', $oldData['id'], $custody['id'], $deduct]);

                    $amountNeeded -= $deduct;
                }

                if ($amountNeeded > 0) {
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
