<?php
require __DIR__.'/config/config.php';
require_permission('expenses.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    try {
        $pdo->beginTransaction();

        $payer_name = trim($_POST['payer_name'] ?? '');
        $payment_source = trim($_POST['payment_source'] ?? '');

        $invoice_serials = $_POST['invoice_serial'] ?? [];
        $invoice_dates   = $_POST['invoice_date'] ?? [];
        $main_expenses   = $_POST['main_expense'] ?? [];
        $sub_expenses    = $_POST['sub_expense'] ?? [];
        $expense_descs   = $_POST['expense_desc'] ?? [];
        $expense_amounts = $_POST['expense_amount'] ?? [];
        $has_vats        = $_POST['has_vat'] ?? [];

        $file_name = upload_image('invoice_image');

        // تأكيد أن في بيانات مصروفات
        if (count($invoice_serials) === 0) {
            throw new Exception("لم يتم إدخال أي مصروفات.");
        }

        foreach ($invoice_serials as $i => $serial) {
            $invoice_serial = trim($serial);
            $invoice_date   = trim($invoice_dates[$i] ?? '');
            $main_expense   = trim($main_expenses[$i] ?? '');
            $sub_expense    = trim($sub_expenses[$i] ?? '');
            $expense_desc   = trim($expense_descs[$i] ?? '');
            $expense_amount = (float)($expense_amounts[$i] ?? 0);
            $has_vat        = (int)($has_vats[$i] ?? 0);

            if ($main_expense === '' || $expense_amount <= 0) continue;

            // رقم تسلسلي جديد
            $lastSerial = $pdo->query("SELECT invoice_serial FROM expenses ORDER BY id DESC LIMIT 1")->fetchColumn();
            if ($lastSerial && preg_match('/DAELE(\d+)/', $lastSerial, $m)) {
                $nextNumber = (int)$m[1] + 1;
            } else {
                $nextNumber = 1;
            }
            $serial_invoice = "DAELE" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

            $vat_value = 0;
            $total_amount = $expense_amount;
            if ($has_vat) {
                $vat_value = $expense_amount * 0.15;
                $total_amount += $vat_value;
            }

            $stmt = $pdo->prepare("
                INSERT INTO expenses(invoice_serial, bill_number, main_expense, sub_expense, expense_desc, expense_amount, vat_value, total_amount, has_vat, expense_file, payer_name, payment_source, created_at)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $serial_invoice,
                $invoice_serial,
                $main_expense,
                $sub_expense,
                $expense_desc,
                $expense_amount,
                $vat_value,
                $total_amount,
                $has_vat,
                $file_name,
                $payer_name,
                $payment_source,
                $invoice_date
            ]);

            $expense_id = $pdo->lastInsertId();

            // خصم من العهدة لو المصدر عهدة
            if ($payment_source === 'عهدة') {
                $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount > 0 ORDER BY taken_at ASC");
                $stmtC->execute([$payer_name]);
                $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                $notes = "مصروفات $main_expense - $sub_expense - $expense_desc";

                $amountToDeduct = $expense_amount + $vat_value;
                foreach ($custodies as $custody) {
                    if ($amountToDeduct <= 0) break;

                    if ($custody['amount'] >= $amountToDeduct) {
                        $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$custody['amount'] - $amountToDeduct, $custody['id']]);
                        $pdo->prepare("
                            INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at)
                            VALUES ('expense', ?, ?, ?, ?, NOW())
                        ")->execute([$expense_id, $custody['id'], $amountToDeduct, $notes]);
                        $amountToDeduct = 0;
                    } else {
                        $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                        $pdo->prepare("
                            INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at)
                            VALUES ('expense', ?, ?, ?, ?, NOW())
                        ")->execute([$expense_id, $custody['id'], $custody['amount'], $notes]);
                        $amountToDeduct -= $custody['amount'];
                    }
                }

                if ($amountToDeduct > 0) {
                    $pdo->rollBack();
                    $_SESSION['toast'] = [
                        'type' => 'danger',
                        'msg'  => 'رصيد العهدة غير كافٍ للشخص: ' . htmlspecialchars($payer_name)
                    ];
                    header('Location: ' . BASE_URL . '/expenses.php');
                    exit;
                }
            }
        }

        $pdo->commit();
        $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت إضافة المصروفات بنجاح.'];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['toast'] = ['type'=>'danger','msg'=>$e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;
?>
