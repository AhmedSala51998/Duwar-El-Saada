<?php
require __DIR__.'/config/config.php'; 
require_permission('assets.edit');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/assetes.php');
        exit;
    }
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
        'quantity' => $quantity,
        'price' => $price,
        'has_vat' => $has_vat,
        'vat_value' => $vat_value,
        'total_amount' => $total_amount,
        'image' => upload_image('image') ?: ($oldData['image'] ?? null),
        'payer_name' => trim($_POST['payer_name'] ?? ''),
        'payment_source' => $_POST['payment_source'] ?? 'كاش'
    ];

    try {
        $pdo->beginTransaction(); // بدء transaction

        // ✅ لو الكمية أصبحت صفر نحذف الأصل تمامًا ونرجع العهدة لو كانت عهدة
        if ($quantity == 0) {
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

                $pdo->prepare("DELETE FROM custody_transactions WHERE type='asset' AND type_id=?")->execute([$oldData['id']]);
            }

            $pdo->prepare("DELETE FROM assets WHERE id=?")->execute([$id]);
            $pdo->commit();

            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم حذف الأصل لأن الكمية أصبحت صفر'];
            header('Location: ' . BASE_URL . '/assetes.php');
            exit;
        }

        // تحقق من التكرار
        $check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name=? AND type=? AND payer_name=? AND id<>?");
        $check->execute([$newData['name'], $newData['type'], $newData['payer_name'], $id]);
        $exists = $check->fetchColumn();

        if($exists > 0){
            throw new Exception('هناك أصل بنفس الاسم، النوع والدافع موجود بالفعل');
        }

        $changed = false;
        foreach(['name','type','quantity','price','has_vat','vat_value','total_amount','image','payer_name','payment_source'] as $key){
            if(!isset($oldData[$key]) || $oldData[$key] != $newData[$key]){
                $changed = true;
                break;
            }
        }

        if($changed){
            // استرجاع العهدة القديمة إذا كانت مدفوعة من العهدة
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
                $pdo->prepare("DELETE FROM custody_transactions WHERE type='asset' AND type_id=?")->execute([$oldData['id']]);
            }

            // خصم العهدة الجديدة إذا كان مصدر الدفع عهدة
            if ($newData['payment_source'] === 'عهدة') {
                $amountNeeded = ($price * $quantity) + $vat_value;

                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$newData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                $notes = "شراء " . $_POST['name'];

                foreach ($custodies as $custody) {
                    if ($amountNeeded <= 0) break;
                    if ($custody['amount'] >= $amountNeeded) {
                        $newAmount = $custody['amount'] - $amountNeeded;
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                        $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
                            ->execute(['asset', $oldData['id'], $custody['id'], $amountNeeded, $notes]);
                        $amountNeeded = 0;
                    } else {
                        $amountDeducted = $custody['amount'];
                        $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                        $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
                            ->execute(['asset', $oldData['id'], $custody['id'], $amountDeducted, $notes]);
                        $amountNeeded -= $amountDeducted;
                    }
                }

                if ($amountNeeded > 0) {
                    //throw new Exception('رصيد العهدة غير كافي');
                    $pdo->rollBack();
                    $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => 'رصيد العهدة غير كافٍ للشخص: ' . htmlspecialchars($newData['payer_name'])
                    ];
                    header('Location: ' . BASE_URL . '/assetes.php');
                    exit;
                }
            }

            // تحديث الأصل
            $pdo->prepare("UPDATE assets SET name=?, type=?, quantity=?, price=?, has_vat=?, vat_value=?, total_amount=?, payer_name=?, payment_source=?, image=? WHERE id=?")
                ->execute([$newData['name'],$newData['type'],$newData['quantity'],$price,$has_vat,$vat_value,$total_amount,$newData['payer_name'],$newData['payment_source'],$newData['image'],$id]);

            $pdo->commit();
            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل الأصل بنجاح'];
        } else {
            $pdo->commit();
            $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'فشل العملية: ' . $e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;
