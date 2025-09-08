<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id'];
    $o = $pdo->prepare("SELECT * FROM expenses WHERE id=?");
    $o->execute([$id]);
    $old = $o->fetch(PDO::FETCH_ASSOC);

    $newData = [
        'main_expense'   => trim($_POST['main_expense']),
        'sub_expense'    => trim($_POST['sub_expense']),
        'expense_desc'   => trim($_POST['expense_desc']),
        'expense_amount' => (float)($_POST['expense_amount'] ?? 0),
        'expense_file'   => upload_image('expense_file') ?: ($old['expense_file'] ?? null)
    ];

    $changed = false;
    foreach ($newData as $k => $v) {
        if ($old[$k] != $v) { $changed = true; break; }
    }

    if($changed){
        $pdo->prepare("UPDATE expenses SET main_expense=?, sub_expense=?, expense_desc=?, expense_amount=?, expense_file=? WHERE id=?")
            ->execute([
                $newData['main_expense'],
                $newData['sub_expense'],
                $newData['expense_desc'],
                $newData['expense_amount'],
                $newData['expense_file'],
                $id
            ]);
        $_SESSION['toast'] = ['type'=>'success','msg'=>'تم التعديل بنجاح'];
    }
}

header('Location: '.BASE_URL.'/expenses.php');
exit;
