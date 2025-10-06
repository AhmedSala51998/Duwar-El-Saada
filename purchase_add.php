<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $names = $_POST['name'] ?? [];
    $units = $_POST['unit'] ?? [];
    $payers = $_POST['payer_name'] ?? [];
    $sources = $_POST['payment_source'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];

    foreach ($names as $i => $name) {
        $name = trim($name);
        $unit = $units[$i] ?? '';
        $payer = trim($payers[$i] ?? '');
        $payment_source = $sources[$i] ?? 'كاش';
        $quantity = (float)($quantities[$i] ?? 0);
        $price = (float)($prices[$i] ?? 0);

        if (!$name || !$unit) continue; // تجاهل أي صف فاضي

        // تحقق من التكرار
        $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE name=? AND unit=? AND payer_name=?");
        $check->execute([$name, $unit, $payer]);
        $exists = $check->fetchColumn();

        if ($exists > 0) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'msg'  => "⚠️ الصنف ($name) موجود بالفعل"
            ];
            continue;
        }

        // خصم العهدة لو مصدر الدفع "عهدة"
        if($payment_source === 'عهدة'){
            $stmtC = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
            $stmtC->execute([$payer]);
            $custody = $stmtC->fetch();
            if($custody && $custody['amount'] >= $price){
                $newAmount = $custody['amount'] - $price;
                $pdo->prepare("UPDATE custodies SET amount=? WHERE id=?")->execute([$newAmount, $custody['id']]);
            } else {
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => "❌ رصيد العهدة غير كافي للصنف ($name)"
                ];
                continue;
            }
        }

        // رفع الصور (لو في ملف لكل صنف)
        $productImage = upload_image('product_image', $i);
        $invoiceImage = upload_image('invoice_image', $i);

        $stmt = $pdo->prepare("
            INSERT INTO purchases (name, quantity, unit, price, product_image, invoice_image, payer_name, payment_source) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $quantity,
            $unit,
            $price,
            $productImage,
            $invoiceImage,
            $payer,
            $payment_source
        ]);
    }

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => '✅ تم حفظ جميع الأصناف'
    ];
}

function upload_image($field, $index = null) {
    if ($index !== null) {
        if (!isset($_FILES[$field]['name'][$index]) || $_FILES[$field]['error'][$index] !== UPLOAD_ERR_OK) {
            return null;
        }
        $fileTmp = $_FILES[$field]['tmp_name'][$index];
        $fileName = time() . "_" . basename($_FILES[$field]['name'][$index]);
    } else {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $fileTmp = $_FILES[$field]['tmp_name'];
        $fileName = time() . "_" . basename($_FILES[$field]['name']);
    }

    $target = __DIR__ . "/uploads/" . $fileName;
    move_uploaded_file($fileTmp, $target);
    return $fileName;
}


header('Location: ' . BASE_URL . '/purchases.php');
exit;
