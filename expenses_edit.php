<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $newData = [
        'main_expense'   => trim($_POST['main_expense']),
        'sub_expense'    => trim($_POST['sub_expense']),
        'expense_desc'   => trim($_POST['expense_desc']),
        'expense_amount' => (float)($_POST['expense_amount'] ?? 0),
        'expense_file'   => upload_image('expense_file') ?: ($oldData['expense_file'] ?? null),
        'payment_source' => $_POST['payment_source'] ?? 'كاش',
        'payer_name'     => trim($_POST['payer_name'] ?? '')
    ];

    // تحقق إذا كان هناك أي تغيير
    $changed = false;
    foreach(['main_expense','sub_expense','expense_desc','expense_amount','expense_file','payment_source','payer_name'] as $key){
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
                $newAmount = $custody['amount'] + $oldData['expense_amount'];
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            }
        }

        // خصم العهدة الجديدة إذا مصدر الدفع "عهدة"
        if($newData['payment_source'] === 'عهدة'){
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
            $stmtC->execute([$newData['payer_name']]);
            $custody = $stmtC->fetch();
            if($custody && $custody['amount'] >= $newData['expense_amount']){
                $newAmount = $custody['amount'] - $newData['expense_amount'];
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            } else {
                $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
                header('Location: ' . BASE_URL . '/expenses.php'); exit;
            }
        }

        // التحديث في قاعدة البيانات
        $pdo->prepare("UPDATE expenses SET main_expense=?, sub_expense=?, expense_desc=?, expense_amount=?, expense_file=?, payment_source=?, payer_name=? WHERE id=?")
            ->execute([
                $newData['main_expense'],
                $newData['sub_expense'],
                $newData['expense_desc'],
                $newData['expense_amount'],
                $newData['expense_file'],
                $newData['payment_source'],
                $newData['payer_name'],
                $id
            ]);

        $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل المصروف بنجاح'];

    } else {
        // لا تغييرات → رسالة معلوماتية
        $_SESSION['toast'] = ['type'=>'info','msg'=>'لا توجد تغييرات للحفظ'];
    }
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
