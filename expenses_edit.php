<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id'];
    $o = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
    $o->execute([$id]);
    $old = $o->fetch(PDO::FETCH_ASSOC);

    // trim and sanitize minimal
    $main = trim($_POST['main_expense'] ?? '');
    $sub  = trim($_POST['sub_expense'] ?? '');
    $desc = trim($_POST['expense_desc'] ?? '');
    $amount = (float)($_POST['expense_amount'] ?? 0);

    // upload_image should be your existing helper that returns filename or null
    $uploaded = upload_image('expense_file');
    $fileToSave = $uploaded ?: ($old['expense_file'] ?? null);

    $newData = [
        'main_expense'   => $main,
        'sub_expense'    => $sub,
        'expense_desc'   => $desc,
        'expense_amount' => $amount,
        'expense_file'   => $fileToSave
    ];

    $changed = false;
    foreach ($newData as $k => $v) {
        if (!isset($old[$k]) || $old[$k] != $v) { $changed = true; break; }
    }

    if($changed){
        $stmt = $pdo->prepare("UPDATE expenses SET main_expense=?, sub_expense=?, expense_desc=?, expense_amount=?, expense_file=? WHERE id=?");
        $stmt->execute([
            $newData['main_expense'],
            $newData['sub_expense'],
            $newData['expense_desc'],
            $newData['expense_amount'],
            $newData['expense_file'],
            $id
        ]);
        $_SESSION['toast'] = ['type'=>'success','msg'=>'تم التعديل بنجاح'];
    } else {
        $_SESSION['toast'] = ['type'=>'info','msg'=>'لا تغييرات للحفظ'];
    }
}

header('Location: '.BASE_URL.'/expenses.php');
exit;
