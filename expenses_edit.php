<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    $old = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $expense_amount = (float)($_POST['expense_amount'] ?? 0);
    $has_vat = isset($_POST['has_vat']) ? (int)$_POST['has_vat'] : 0;
    $vat_value = 0;
    $total_amount = $expense_amount;

    if ($has_vat) {
        $vat_value = $expense_amount * 0.15;
        $total_amount = $expense_amount + $vat_value;
    }

    $newData = [
        'main_expense'   => trim($_POST['main_expense']),
        'sub_expense'    => trim($_POST['sub_expense']),
        'expense_desc'   => trim($_POST['expense_desc']),
        'expense_amount' => $expense_amount,
        'has_vat'        => $has_vat,
        'vat_value'      => $vat_value,
        'total_amount'   => $total_amount,
        'expense_file'   => upload_image('expense_file') ?: ($oldData['expense_file'] ?? null),
        'payment_source' => $_POST['payment_source'] ?? 'كاش',
        'payer_name'     => trim($_POST['payer_name'] ?? '')
    ];

    $changed = false;
    foreach($newData as $key=>$val){
        if(!isset($oldData[$key]) || $oldData[$key] != $val){
            $changed = true;
            break;
        }
    }

    if($changed){
        // استرجاع العهدة القديمة إذا كانت مدفوعة من العهدة
        if($oldData['payment_source'] === 'عهدة'){
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at ASC");
            $stmtC->execute([$oldData['payer_name']]);
            $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

            $amountToReturn = $oldData['expense_amount'];
            foreach($custodies as $custody){
                if($amountToReturn <= 0) break;

                $newAmount = $custody['amount'] + $amountToReturn;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                // سجل العملية في custody_transactions
                $stmtTx = $pdo->prepare("
                    INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmtTx->execute(['refund', $oldData['id'], $custody['id'], $amountToReturn]);

                $amountToReturn = 0; // بعد الإضافة يمكن التوقف
            }
        }

        // خصم العهدة الجديدة إذا مصدر الدفع "عهدة"
        if($newData['payment_source'] === 'عهدة'){
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
            $stmtC->execute([$newData['payer_name']]);
            $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

            $amountToDeduct = $newData['expense_amount'];
            foreach($custodies as $custody){
                if($amountToDeduct <= 0) break;

                $deduct = min($custody['amount'], $amountToDeduct);
                $newAmount = $custody['amount'] - $deduct;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

                // سجل العملية في custody_transactions
                $stmtTx = $pdo->prepare("
                    INSERT INTO custody_transactions (type, type_id, custody_id, amount, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmtTx->execute(['deduct', $oldData['id'], $custody['id'], $deduct]);

                $amountToDeduct -= $deduct;
            }

            if($amountToDeduct > 0){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                header('Location: ' . BASE_URL . '/expenses.php');
                exit;
            }
        }


        $pdo->prepare("UPDATE expenses SET main_expense=?, sub_expense=?, expense_desc=?, expense_amount=?, has_vat=?, vat_value=?, total_amount=?, expense_file=?, payment_source=?, payer_name=? WHERE id=?")
            ->execute([
                $newData['main_expense'],
                $newData['sub_expense'],
                $newData['expense_desc'],
                $newData['expense_amount'],
                $newData['has_vat'],
                $newData['vat_value'],
                $newData['total_amount'],
                $newData['expense_file'],
                $newData['payment_source'],
                $newData['payer_name'],
                $id
            ]);

        $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل المصروف بنجاح'];
    } else {
        $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
    }
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;