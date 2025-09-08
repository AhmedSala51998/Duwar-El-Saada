<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $main_expense = trim($_POST['main_expense']);
    $sub_expense  = trim($_POST['sub_expense']);
    $expense_desc = trim($_POST['expense_desc']);
    $expense_amount = (float)($_POST['expense_amount'] ?? 0);

    $pdo->prepare("INSERT INTO expenses(main_expense,sub_expense,expense_desc,expense_amount,expense_file) VALUES(?,?,?,?,?)")
        ->execute([
            $main_expense,
            $sub_expense,
            $expense_desc,
            $expense_amount,
            upload_image('expense_file')
        ]);

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت الإضافة بنجاح'];
}

header('Location: '.BASE_URL.'/expenses.php');
exit;
