<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM rentals WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    // تجهيز البيانات الجديدة
    $newData = [
        'rental_name'  => trim($_POST['rental_name']),
        'payment_type' => $_POST['payment_type'] ?? '',
        'rental_price' => (float)($_POST['rental_price'] ?? 0),
        'rental_kind'  => trim($_POST['rental_kind'] ?? ''),
        'payer'        => $_POST['payer'] ?? '',
        'invoice_image'=> upload_image('invoice_image') ?: ($oldData['invoice_image'] ?? null)
    ];

    // التحقق من التكرار (باستثناء السجل الحالي)
    $check = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE rental_name=? AND payment_type=? AND rental_kind=? AND payer=? AND id<>?");
    $check->execute([
        $newData['rental_name'],
        $newData['payment_type'],
        $newData['rental_kind'],
        $newData['payer'],
        $id
    ]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هذا الإيجار موجود بالفعل بنفس المواصفات'
        ];
    } else {
        // التحقق من وجود تغييرات
        $changed = false;
        foreach($newData as $key => $value){
            if($oldData[$key] != $value){
                $changed = true;
                break;
            }
        }

        // تنفيذ التحديث فقط لو هناك تغييرات
        if($changed){
            $stmt = $pdo->prepare("
                UPDATE rentals 
                SET rental_name=?, payment_type=?, rental_price=?, rental_kind=?, payer=?, invoice_image=? 
                WHERE id=?
            ");
            $stmt->execute([
                $newData['rental_name'],
                $newData['payment_type'],
                $newData['rental_price'],
                $newData['rental_kind'],
                $newData['payer'],
                $newData['invoice_image'],
                $id
            ]);

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
        }
    }
}

header('Location: rentals.php');
exit;
