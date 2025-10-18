<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    // ✅ منع تنفيذ أي تعديل إذا المستخدم قفل المودال أو لم يضغط "حفظ"
    if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);

    // جلب البيانات القديمة
    $stmtOld = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
    $stmtOld->execute([$id]);
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'المنتج غير موجود'];
        header('Location: ' . BASE_URL . '/purchases.php');
        exit;
    }

    // البيانات الجديدة من الفورم
    $newData = [
        'name'           => trim($_POST['name']),
        'quantity'       => (float)($_POST['quantity'] ?? 0),
        'single_quantity'=> (float)($_POST['single_quantity'] ?? 0),
        'unit'           => $_POST['unit'] ?? '',
        'price'          => (float)($_POST['price'] ?? 0),
        'product_image'  => upload_image('product_image') ?: ($oldData['product_image'] ?? null),
        'invoice_image'  => upload_image('invoice_image') ?: ($oldData['invoice_image'] ?? null),
        'payer_name'     => trim($_POST['payer_name'] ?? ''),
        'payment_source' => $_POST['payment_source'] ?? 'كاش',
        'package'        => trim($_POST['package'] ?? '')
    ];

    // جلب أعلى كمية مرتبطة بإذن صرف
    $stmtIssued = $pdo->prepare("SELECT MAX(qty) FROM orders WHERE purchase_id=?");
    $stmtIssued->execute([$id]);
    $maxIssuedQty = (float)$stmtIssued->fetchColumn();

    // استرجاع العهدة القديمة إذا كانت مدفوعة من العهدة
    if ($oldData['payment_source'] === 'عهدة') {
        $stmtTx = $pdo->prepare("SELECT * FROM custody_transactions WHERE type='purchase' AND type_id=?");
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

        // حذف المعاملات القديمة
        $pdo->prepare("DELETE FROM custody_transactions WHERE type='purchase' AND type_id=?")->execute([$oldData['id']]);
    }

    // منطق الحذف أو تعديل الكمية
    if ($newData['quantity'] == 0) {
        if ($maxIssuedQty > 0) {
            $_SESSION['toast'] = ['type'=>'danger', 'msg'=>'لا يمكن حذف المنتج، يوجد إذن صرف مرتبط، امسح إذن الصرف أولًا'];
        } else {
            // استدعاء كود الحذف القديم مباشرة
            require __DIR__.'/purchase_delete_logic.php';
        }
    } elseif ($newData['quantity'] < $oldData['total_packages']) {
        if ($newData['quantity'] < $maxIssuedQty) {
            $_SESSION['toast'] = ['type'=>'danger','msg'=>'لا يمكن تعديل الكمية، مرتبطة بإذن صرف أكبر من الكمية الجديدة، امسح إذن الصرف أولًا'];
        } else {
            // تعديل آمن: تقليل أو زيادة بدون المساس بإذن الصرف
            /*$addedQty = $newData['quantity'] - $oldData['total_packages'];
            $result_data = $addedQty * $newData['single_quantity'];
            $newPrintingQty = $oldData['prinitng_quantity'] + $result_data;
            $unit_quantity = $oldData['quantity'] + $result_data;
            $unit_price = $newData['price'] / $newData['single_quantity'];*/

            $oldQuantity = $oldData['total_packages'];
            $oldSingleQty = $oldData['single_package'];
            $oldPrintingQty = $oldData['prinitng_quantity'];
            $oldUnitQuantity = $oldData['quantity']; // quantity بالوحدات الكاملة
            $newQuantity = $newData['quantity'];
            $newSingleQty = $newData['single_quantity'];
            $newPrice = $newData['price'];

            // أربع احتمالات
            if ($newQuantity != $oldQuantity && $newSingleQty == $oldSingleQty) {
                // تغيير الكمية فقط
                $addedQty = $newQuantity - $oldQuantity;
                $result_data = $addedQty * $newSingleQty;
                $newPrintingQty = $oldPrintingQty + $result_data;
                $unit_quantity = $oldUnitQuantity + $result_data;
                $unit_price = $newPrice / $newSingleQty;

            } elseif ($newQuantity == $oldQuantity && $newSingleQty != $oldSingleQty) {
                // تغيير single_quantity فقط
                $result_data = $newQuantity * ($newSingleQty - $oldSingleQty);
                $newPrintingQty = $oldPrintingQty + $result_data;
                $unit_quantity = $oldUnitQuantity + $result_data;
                $unit_price = $newPrice / $newSingleQty;

            } elseif ($newQuantity != $oldQuantity && $newSingleQty != $oldSingleQty) {
                // تغيير كلاهما
                $addedQty = $newQuantity - $oldQuantity;
                $result_data = $addedQty * $newSingleQty;
                $newPrintingQty = $oldPrintingQty + $result_data;
                $unit_quantity = $oldUnitQuantity + $result_data;
                $unit_price = $newPrice / $newSingleQty;

            } else {
                // لا تغيير
                $newPrintingQty = $oldPrintingQty;
                $unit_quantity = $oldUnitQuantity;
                $unit_price = $newPrice / $newSingleQty;
            }

            // حساب الضريبة والقيم النهائية
            $vatRate = 0.15;
            $unit_total = $unit_quantity * $unit_price;
            $unit_vat = $unit_total * $vatRate;
            $unit_all_total = $unit_total + $unit_vat;


            // خصم العهدة الجديدة إذا مصدر الدفع عهدة
            if ($newData['payment_source'] === 'عهدة') {
                $amountNeeded = ($newData['price'] * $newData['quantity']) + $unit_vat;
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$newData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                $notes = "شراء " . $_POST['name'];

                foreach ($custodies as $custody) {
                    if ($amountNeeded <= 0) break;
                    if ($custody['amount'] >= $amountNeeded) {
                        $newAmount = $custody['amount'] - $amountNeeded;
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                        $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount,notes, created_at) VALUES (?, ?, ?, ?,?, NOW())")
                            ->execute(['purchase', $oldData['id'], $custody['id'], $amountNeeded, $notes]);
                        $amountNeeded = 0;
                    } else {
                        $amountDeducted = $custody['amount'];
                        $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                        $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount,notes, created_at) VALUES (?, ?, ?, ?,?, NOW())")
                            ->execute(['purchase', $oldData['id'], $custody['id'], $amountDeducted, $notes]);
                        $amountNeeded -= $amountDeducted;
                    }
                }

                if ($amountNeeded > 0) {
                    $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                    header('Location: ' . BASE_URL . '/purchases.php'); 
                    exit;
                }
            }

            // تحديث purchase
            $pdo->prepare("UPDATE purchases SET 
                name=?, quantity=?, prinitng_quantity=?, single_package=?, total_packages=?, unit=?, package=?, price=?, total_price=?, product_image=?, invoice_image=?, payer_name=?, payment_source=?, unit_total=?, unit_vat=?, unit_all_total=?
                WHERE id=?")
            ->execute([
                $newData['name'],
                $unit_quantity,
                $oldPrintingQty,
                $newData['single_quantity'],
                $newData['quantity'],
                $newData['unit'],
                $newData['package'],
                $unit_price,
                $newData['price'],
                $newData['product_image'],
                $newData['invoice_image'],
                $newData['payer_name'],
                $newData['payment_source'],
                $unit_total,
                $unit_vat,
                $unit_all_total,
                $id
            ]);

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل الكمية بنجاح'];
        }
        
    } else {
        // زيادة على القديم
        /*$addedQty = $newData['quantity'] - $oldData['total_packages'];
        $result_data = $addedQty * $newData['single_quantity'];
        $newPrintingQty = $oldData['prinitng_quantity'] + $result_data;
        $unit_quantity = $oldData['quantity'] + $result_data;
        $unit_price = $newData['price'] / $newData['single_quantity'];*/

        $oldQuantity = $oldData['total_packages'];
        $oldSingleQty = $oldData['single_package'];
        $oldPrintingQty = $oldData['prinitng_quantity'];
        $oldUnitQuantity = $oldData['quantity']; // quantity بالوحدات الكاملة
        $newQuantity = $newData['quantity'];
        $newSingleQty = $newData['single_quantity'];
        $newPrice = $newData['price'];

        if ($newQuantity != $oldQuantity && $newSingleQty == $oldSingleQty) {
            // تغيير الكمية فقط
            $addedQty = $newQuantity - $oldQuantity;
            $result_data = $addedQty * $newSingleQty;
            $newPrintingQty = $oldPrintingQty + $result_data;
            $unit_quantity = $oldUnitQuantity + $result_data;
            $unit_price = $newPrice / $newSingleQty;

        } elseif ($newQuantity == $oldQuantity && $newSingleQty != $oldSingleQty) {
            // تغيير single_quantity فقط
            $result_data = $newQuantity * ($newSingleQty - $oldSingleQty);
            $newPrintingQty = $oldPrintingQty + $result_data;
            $unit_quantity = $oldUnitQuantity + $result_data;
            $unit_price = $newPrice / $newSingleQty;

        } elseif ($newQuantity != $oldQuantity && $newSingleQty != $oldSingleQty) {
            // تغيير كلاهما
            $addedQty = $newQuantity - $oldQuantity;
            $result_data = $addedQty * $newSingleQty;
            $newPrintingQty = $oldPrintingQty + $result_data;
            $unit_quantity = $oldUnitQuantity + $result_data;
            $unit_price = $newPrice / $newSingleQty;

        } else {
            // لا تغيير
            $newPrintingQty = $oldPrintingQty;
            $unit_quantity = $oldUnitQuantity;
            $unit_price = $newPrice / $newSingleQty;
        }

        // حساب الضريبة والقيم النهائية
        $vatRate = 0.15;
        $unit_total = $unit_quantity * $unit_price;
        $unit_vat = $unit_total * $vatRate;
        $unit_all_total = $unit_total + $unit_vat;

        // خصم العهدة الجديدة إذا مصدر الدفع عهدة
        if ($newData['payment_source'] === 'عهدة') {
            $amountNeeded = ($newData['price'] * $newData['quantity']) + $unit_vat;
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
            $stmtC->execute([$newData['payer_name']]);
            $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);
            $notes = "شراء " . $_POST['name'];

            foreach ($custodies as $custody) {
                if ($amountNeeded <= 0) break;
                if ($custody['amount'] >= $amountNeeded) {
                    $newAmount = $custody['amount'] - $amountNeeded;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                    $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount,notes, created_at) VALUES (?, ?, ?, ?,?, NOW())")
                        ->execute(['purchase', $oldData['id'], $custody['id'], $amountNeeded, $notes]);
                    $amountNeeded = 0;
                } else {
                    $amountDeducted = $custody['amount'];
                    $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                    $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount,notes, created_at) VALUES (?, ?, ?, ?,?, NOW())")
                        ->execute(['purchase', $oldData['id'], $custody['id'], $amountDeducted, $notes]);
                    $amountNeeded -= $amountDeducted;
                }
            }

            if ($amountNeeded > 0) {
                $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                header('Location: ' . BASE_URL . '/purchases.php'); 
                exit;
            }
        }


        // تحديث purchase
        $pdo->prepare("UPDATE purchases SET 
            name=?, quantity=?, prinitng_quantity=?, single_package=?, total_packages=?, unit=?, package=?, price=?, total_price=?, product_image=?, invoice_image=?, payer_name=?, payment_source=?, unit_total=?, unit_vat=?, unit_all_total=?
            WHERE id=?")
        ->execute([
            $newData['name'],
            $unit_quantity,
            $oldPrintingQty,
            $newData['single_quantity'],
            $newData['quantity'],
            $newData['unit'],
            $newData['package'],
            $unit_price,
            $newData['price'],
            $newData['product_image'],
            $newData['invoice_image'],
            $newData['payer_name'],
            $newData['payment_source'],
            $unit_total,
            $unit_vat,
            $unit_all_total,
            $id
        ]);

        $_SESSION['toast'] = ['type'=>'success','msg'=>'تم زيادة الكمية بنجاح'];
    }

    // ✅ إعادة حساب إجمالي الفاتورة
    if (!empty($oldData['order_id'])) {
        $orderId = $oldData['order_id'];
        $stmtItems = $pdo->prepare("SELECT quantity, price FROM purchases WHERE order_id=?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['price'];
        }

        $vat = $total * 0.15;
        $allTotal = $total + $vat;

        $pdo->prepare("UPDATE orders_purchases SET total=?, vat=?, all_total=? WHERE id=?")
            ->execute([$total, $vat, $allTotal, $orderId]);
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
