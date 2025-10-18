<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    if (!isset($_POST['save'])) {
        header('Location: ' . BASE_URL . '/expenses.php');
        exit;
    }

    $bill_number = trim($_POST['bill_number'] ?? '');

    if ($bill_number !== '') {
        // فحص التكرار
        $check = $pdo->prepare("SELECT id FROM expenses WHERE bill_number = ?");
        $check->execute([$bill_number]);
        if ($check->fetch()) {
            $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'رقم فاتورة المورد مكرر بالفعل'];
            header('Location: ' . BASE_URL . '/expenses.php');
            exit;
        }
    }

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

    $pdo->prepare("INSERT INTO expenses(bill_number, invoice_serial, main_expense, sub_expense, expense_desc, expense_amount, vat_value, total_amount, has_vat, expense_file, payer_name, payment_source)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)")
    ->execute([
        $bill_number,
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
    $expense_id = $pdo->lastInsertId();

    // خصم العهدة إذا مصدر الدفع "عهدة"
    if($payment_source === 'عهدة'){
        // جلب كل العهد المتاحة للشخص
        $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
        $stmtC->execute([$payer_name]);
        $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        $notes = "مصروفات " . $main_expense . "-" . $sub_expense . "-" . $expense_desc;

        $amountToDeduct = $expense_amount + $vat_value;
        foreach($custodies as $custody){
            if($amountToDeduct <= 0) break;

            $deduct = min($custody['amount'], $amountToDeduct);
            $newAmount = $custody['amount'] - $deduct;
            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);

            // تسجيل المعاملة في الجدول الوسيط
            $stmtTx = $pdo->prepare("
                INSERT INTO custody_transactions (type, type_id, custody_id, amount , notes, created_at)
                VALUES (?, ?, ?, ?,?, NOW())
            ");
            $stmtTx->execute(['expense', $expense_id, $custody['id'], $deduct , $notes]);

            $amountToDeduct -= $deduct;
        }

        if($amountToDeduct > 0){
            $_SESSION['toast'] = ['type'=>'danger','msg'=>'رصيد العهدة غير كافي'];
            header('Location: ' . BASE_URL . '/expenses.php');
            exit;
        }
    }

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت الإضافة بنجاح'];
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
