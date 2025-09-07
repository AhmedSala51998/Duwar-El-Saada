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
        $required = ['name','quantity','unit','price','payer_name','image_path','invoice_path'];
        foreach($required as $col){
            if(!in_array($col,$header)){
                $_SESSION['toast'] = ['type'=>'danger','msg'=>"❌ الملف لا يحتوي على العمود: $col"];
                header('Location: ' . BASE_URL . '/purchases.php'); exit;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO purchases (name, quantity, unit, price, payer_name, product_image, invoice_image, created_at) VALUES (?,?,?,?,?,?,?,NOW())");
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

            $stmt->execute([
                $data['name'] ?? null,
                $data['quantity'] ?? null,
                $data['unit'] ?? null,
                $data['price'] ?? null,
                $data['payer_name'] ?? null,
                $data['image_path'] ?? null,
                $data['invoice_path'] ?? null,
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
