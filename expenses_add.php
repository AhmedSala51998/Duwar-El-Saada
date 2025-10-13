<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $main_expense = trim($_POST['main_expense']);
    $sub_expense  = trim($_POST['sub_expense']);
    $expense_desc = trim($_POST['expense_desc']);
    $expense_amount = (float)($_POST['expense_amount'] ?? 0);
    $has_vat = isset($_POST['has_vat']) ? (int)$_POST['has_vat'] : 0;
    $vat_value = 0;
    $total_amount = $expense_amount;

    // إنشاء رقم تسلسلي للفواتير بصيغة DAEL00001
    $lastSerial = $pdo->query("SELECT invoice_serial FROM expenses ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($lastSerial && preg_match('/DAELE(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    } else {
        $nextNumber = 1;
    }
    $serial_invoice = "DAELE" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    if ($has_vat) {
        $vat_value = $expense_amount * 0.15;
        $total_amount = $expense_amount + $vat_value;
    }

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

    $pdo->prepare("INSERT INTO expenses(invoice_serial, main_expense, sub_expense, expense_desc, expense_amount, vat_value, total_amount, has_vat, expense_file, payer_name, payment_source)
                VALUES(?,?,?,?,?,?,?,?,?,?,?)")
        ->execute([
            $serial_invoice,
            $main_expense,
            $sub_expense,
            $expense_desc,
            $expense_amount,
            $vat_value,
            $total_amount,
            $has_vat,
            upload_image('expense_file'),
            $payer_name,
            $payment_source
        ]);

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت الإضافة بنجاح'];
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
