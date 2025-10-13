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
        'payment_source'=> $_POST['payment_source'] ?? 'كاش',
        'package' => trim($_POST['package'] ?? '')
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
        foreach(['name','quantity','unit','package','price','product_image','invoice_image','payer_name','payment_source'] as $key){
            if(!isset($oldData[$key]) || $oldData[$key] != $newData[$key]){
                $changed = true;
                break;
            }
        }

        if($changed){

            // استرجاع العهدة القديمة إذا كانت مدفوعة من العهدة
            // استرجاع العهدة القديمة
            if ($oldData['payment_source'] === 'عهدة') {
                $refund = $oldData['price'] * $oldData['quantity'];

                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
                $stmtC->execute([$oldData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                foreach ($custodies as $custody) {
                    $newAmount = $custody['amount'] + $refund;
                    $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                    $refund = 0; // بعد الإضافة يمكننا التوقف أو نكمل التوزيع حسب السياسات
                }
            }

            // خصم العهدة الجديدة
            if ($newData['payment_source'] === 'عهدة') {
                $amountNeeded = $newData['price'] * $newData['quantity'];

                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
                $stmtC->execute([$newData['payer_name']]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                $deducted = false;
                foreach ($custodies as $custody) {
                    if ($custody['amount'] >= $amountNeeded) {
                        $newAmount = $custody['amount'] - $amountNeeded;
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                        $deducted = true;
                        break;
                    }
                }

                if (!$deducted) {
                    $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                    header('Location: ' . BASE_URL . '/purchases.php'); 
                    exit;
                }
            }

            // التحديث في purchases
            $pdo->prepare("
                UPDATE purchases 
                SET name=?, quantity=?, unit=?, package=?, price=?, product_image=?, invoice_image=?, payer_name=?, payment_source=? 
                WHERE id=?
            ")->execute([
                $newData['name'],
                $newData['quantity'],
                $newData['unit'],
                $newData['package'],     // هنا
                $newData['price'],
                $newData['product_image'],
                $newData['invoice_image'],
                $newData['payer_name'],
                $newData['payment_source'],
                $id
            ]);


            // ✅ إعادة حساب إجمالي الفاتورة
            if (!empty($oldData['order_id'])) {
                $orderId = $oldData['order_id'];

                // جمع كل الأصناف المرتبطة بالفاتورة
                $stmtItems = $pdo->prepare("SELECT quantity, price FROM purchases WHERE order_id=?");
                $stmtItems->execute([$orderId]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                $total = 0;
                foreach ($items as $item) {
                    $total += $item['quantity'] * $item['price'];
                }

                // افترض أن الضريبة ثابتة 15%
                $vat = $total * 0.15;
                $allTotal = $total + $vat;

                // تحديث جدول orders_purchases
                $pdo->prepare("UPDATE orders_purchases SET total=?, vat=?, all_total=? WHERE id=?")
                    ->execute([$total, $vat, $allTotal, $orderId]);
            }

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل العملية وحساب الفاتورة بنجاح'];

        } else {
            $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
        }
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
