<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config/config.php';
require_permission('expenses.addExpenseExcel');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ لم يتم رفع ملف Excel بشكل صحيح'];
        header('Location: ' . BASE_URL . '/expenses.php');
        exit;
    }

    require_once __DIR__ . '/libs/SimpleXLSX.php';
    $filePath = $_FILES['excel_file']['tmp_name'];

    $payer_name      = trim($_POST['payer_name'] ?? '');
    $payment_source  = trim($_POST['payment_source'] ?? 'كاش');
    $invoiceImage    = upload_image('invoice_image');
    $branch_id      = (int)($_POST['branch_id'] ?? 0);

    if ($xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
        $rows = $xlsx->rows();
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        // الأعمدة المطلوبة
        $required = ['invoice_serial','invoice_date','main_expense','sub_expense','expense_desc','expense_amount','has_vat'];
        foreach($required as $col){
            if(!in_array($col,$header)){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ الملف لا يحتوي على العمود: $col"];
                header('Location: ' . BASE_URL . '/expenses.php');
                exit;
            }
        }

        // توليد رقم تسلسلي للفواتير
        $lastSerial = $pdo->query("SELECT invoice_serial FROM expenses ORDER BY id DESC LIMIT 1")->fetchColumn();
        if ($lastSerial && preg_match('/DAELE(\d+)/', $lastSerial, $m)) {
            $nextNumber = (int)$m[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $baseSerial = $nextNumber;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO expenses (branch_id, invoice_serial, bill_number, main_expense, sub_expense, expense_desc, expense_amount, vat_value, total_amount, has_vat, payer_name, payment_source, expense_file, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)
            ");

            foreach ($rows as $r) {
                $data = array_combine($header, $r);
                $main_expense   = trim($data['main_expense']);
                $sub_expense    = trim($data['sub_expense'] ?? '');
                $expense_desc   = trim($data['expense_desc'] ?? '');
                $expense_amount = (float)($data['expense_amount'] ?? 0);
                $has_vat        = (int)($data['has_vat'] ?? 0);
                $invoice_serial = trim($data['invoice_serial'] ?? '');
                if ($invoice_serial !== '') {
                    // فحص التكرار
                    $check = $pdo->prepare("SELECT id FROM expenses WHERE bill_number = ?");
                    $check->execute([$invoice_serial]);
                    if ($check->fetch()) {
                        $pdo->rollBack();
                        $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'رقم فاتورة المورد مكرر بالفعل'];
                        header('Location: ' . BASE_URL . '/expenses.php');
                        exit;
                    }
                }
                $invoice_date   = trim($data['invoice_date'] ?? date('Y-m-d'));

                if (!$main_expense || $expense_amount <= 0) continue;

                // رقم تسلسلي جديد لكل فاتورة
                $serial_invoice = "DAELE" . str_pad($baseSerial, 5, "0", STR_PAD_LEFT);
                $baseSerial++;

                // الضريبة والإجمالي
                $vat_value = $has_vat ? $expense_amount * 0.15 : 0;
                $total_amount = $expense_amount + $vat_value;

                $stmt->execute([
                    $branch_id,
                    $serial_invoice,
                    $invoice_serial,
                    $main_expense,
                    $sub_expense,
                    $expense_desc,
                    $expense_amount,
                    $vat_value,
                    $total_amount,
                    $has_vat,
                    $payer_name,
                    $payment_source,
                    $invoiceImage,
                    $invoice_date
                ]);

                $expense_id = $pdo->lastInsertId();

                require_once __DIR__.'/libs/activity_log.php';

                add_activity_log(
                    $pdo,
                    'add_excel',
                    'expenses',
                    $expense_id,
                    "إضافة مصروفات - {$serial_invoice}"
                );

                // التعامل مع العهدة
                if ($payment_source === 'عهدة') {
                    $amountToDeduct = $total_amount;
                    $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND branch_id=? AND amount > 0 ORDER BY taken_at ASC");
                    $stmtC->execute([$payer_name , $branch_id]);
                    $custodies = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                    $totalAvailable = array_sum(array_column($custodies, 'amount'));
                    if($totalAvailable < $amountToDeduct){
                        $pdo->rollBack();
                        $_SESSION['toast'] = [
                            'type' => 'danger',
                            'msg'  => 'رصيد العهدة غير كافٍ للشخص: ' . htmlspecialchars($payer_name)
                        ];
                        header('Location: ' . BASE_URL . '/expenses.php');
                        exit;
                    }

                    foreach ($custodies as $custody) {
                        if ($amountToDeduct <= 0) break;
                        $notes = "مصروفات " . $main_expense . " - " . $sub_expense . " - " . $expense_desc;

                        if ($custody['amount'] >= $amountToDeduct) {
                            $newAmount = $custody['amount'] - $amountToDeduct;
                            $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
                            $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at)
                                           VALUES ('expense', ?, ?, ?, ?, NOW())")
                                ->execute([$expense_id, $custody['id'], $amountToDeduct, $notes]);
                            $amountToDeduct = 0;
                        } else {
                            $amountDeducted = $custody['amount'];
                            $pdo->prepare("UPDATE custodies SET amount=0 WHERE id=?")->execute([$custody['id']]);
                            $pdo->prepare("INSERT INTO custody_transactions (type, type_id, custody_id, amount, notes, created_at)
                                           VALUES ('expense', ?, ?, ?, ?, NOW())")
                                ->execute([$expense_id, $custody['id'], $amountDeducted, $notes]);
                            $amountToDeduct -= $amountDeducted;
                        }
                    }
                }
            }

            $pdo->commit();
            $_SESSION['toast'] = ['type'=>'success','msg'=>"✅ تم استيراد المصروفات وإنشاء الفواتير بنجاح"];

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ فشل العملية: " . $e->getMessage()];
        }

    } else {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ خطأ في قراءة الملف: ".\Shuchkin\SimpleXLSX::parseError()];
    }

} else {
    $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ طلب غير صالح'];
}

header('Location: ' . BASE_URL . '/expenses.php');
exit;


// 🧩 دالة رفع الصورة
function upload_image($field) {
    if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES[$field]['tmp_name'];
        $fileName = time() . "_" . basename($_FILES[$field]['name']);
        $target = __DIR__ . "/uploads/" . $fileName;
        move_uploaded_file($fileTmp, $target);
        return $fileName;
    }
    return null;
}
?>
