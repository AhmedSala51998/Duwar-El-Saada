<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $main_expense = trim($_POST['main_expense']);
    $sub_expense  = trim($_POST['sub_expense']);
    $expense_desc = trim($_POST['expense_desc']);
    $expense_amount = (float)($_POST['expense_amount'] ?? 0);
    $payment_source = $_POST['payment_source'] ?? 'كاش';
    $payer_name = $_POST['payer_name'] ?? null;

    // خصم العهدة إذا مصدر الدفع "عهدة"
    if($payment_source === 'عهدة'){
        $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
        $stmtC->execute([$payer_name]);
        $custody = $stmtC->fetch();
        if($custody && $custody['amount'] >= $expense_amount){
            $newAmount = $custody['amount'] - $expense_amount;
            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
        } else {
            $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
            header('Location: ' . BASE_URL . '/expenses.php'); exit;
        }
    }

    $pdo->prepare("INSERT INTO expenses(main_expense, sub_expense, expense_desc, expense_amount, expense_file,payer_name, payment_source) VALUES(?,?,?,?,?,? , ?)")
        ->execute([
            $main_expense,
            $sub_expense,
            $expense_desc,
            $expense_amount,
            upload_image('expense_file'),
            $payer_name,
            $payment_source
        ]);

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت الإضافة بنجاح'];
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
