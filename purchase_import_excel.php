<?php
require __DIR__ . '/config/config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ لم يتم رفع الملف بشكل صحيح'];
        header('Location: ' . BASE_URL . '/purchases.php'); exit;
    }

    require_once __DIR__ . '/libs/SimpleXLSX.php';
    $filePath = $_FILES['excel_file']['tmp_name'];
    
    if ($xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
        $rows = $xlsx->rows();
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        $required = ['name','quantity','unit','price','payer_name','payment_source','image_path','invoice_path'];
        foreach($required as $col){
            if(!in_array($col,$header)){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ الملف لا يحتوي على العمود: $col"];
                header('Location: ' . BASE_URL . '/purchases.php'); exit;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO purchases (name, quantity, unit, price, payer_name, payment_source, product_image, invoice_image, custody_used, remaining_to_pay, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
        $count = 0;

        foreach($rows as $r){
            $data = array_combine($header,$r);

            // نسخ صورة المنتج
            $productImagePath = null;
            if(!empty($data['image_path']) && file_exists($data['image_path'])){
                $ext = pathinfo($data['image_path'], PATHINFO_EXTENSION);
                $productImagePath = 'uploads/' . uniqid() . '.' . $ext;
                copy($data['image_path'], __DIR__ . '/' . $productImagePath);
            }

            // نسخ صورة الفاتورة
            $invoiceImagePath = null;
            if(!empty($data['invoice_path']) && file_exists($data['invoice_path'])){
                $ext = pathinfo($data['invoice_path'], PATHINFO_EXTENSION);
                $invoiceImagePath = 'uploads/' . uniqid() . '.' . $ext;
                copy($data['invoice_path'], __DIR__ . '/' . $invoiceImagePath);
            }

            $price = (float)($data['price'] ?? 0);
            $payer_name = trim($data['payer_name'] ?? '');
            $payment_source = trim($data['payment_source'] ?? 'مالك');

            $deduct_from_custody = 0;
            $remaining_to_pay = $price;

            // تطبيق منطق العهدة
            if($payment_source === 'عهدة' && $payer_name){
                $stmt_custody = $pdo->prepare("SELECT amount, id FROM custodies WHERE person_name=? ORDER BY taken_at DESC LIMIT 1");
                $stmt_custody->execute([$payer_name]);
                $custody = $stmt_custody->fetch(PDO::FETCH_ASSOC);

                if($custody && $custody['amount'] > 0){
                    if($custody['amount'] >= $price){
                        $deduct_from_custody = $price;
                        $remaining_to_pay = 0;
                    } else {
                        $deduct_from_custody = $custody['amount'];
                        $remaining_to_pay = $price - $custody['amount'];
                    }
                    // خصم من العهدة
                    $pdo->prepare("UPDATE custodies SET amount = amount - ? WHERE id=?")->execute([$deduct_from_custody, $custody['id']]);
                } else {
                    // لا يوجد رصيد → تحويل المصدر للمالك
                    $payment_source = 'مالك';
                    $deduct_from_custody = 0;
                    $remaining_to_pay = $price;
                }
            }

            $stmt->execute([
                $data['name'] ?? null,
                $data['quantity'] ?? null,
                $data['unit'] ?? null,
                $price,
                $payer_name,
                $payment_source,
                $productImagePath,
                $invoiceImagePath,
                $deduct_from_custody,
                $remaining_to_pay
            ]);
            $count++;
        }

        $_SESSION['toast'] = ['type'=>'success','msg'=>"✅ تم استيراد $count صنف بنجاح"];
    } else {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ خطأ في قراءة الملف: ".\Shuchkin\SimpleXLSX::parseError()];
    }
} else {
    $_SESSION['toast'] = ['type'=>'danger','msg'=>'❌ طلب غير صالح'];
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
