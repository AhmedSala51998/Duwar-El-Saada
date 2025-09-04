<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM gov_fees WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    // البيانات الجديدة
    $newData = [
        'fee_title'    => trim($_POST['fee_title']),
        'fee_type'     => trim($_POST['fee_type']),
        'fee_amount'   => (float)($_POST['fee_amount'] ?? 0),
        'payer'        => $_POST['payer'],
        'invoice_image'=> upload_image('invoice_image') ?: $oldData['invoice_image']
    ];

    // التحقق من وجود تغييرات
    $changed = false;
    foreach($newData as $key => $value){
        if($oldData[$key] != $value){
            $changed = true;
            break;
        }
    }

    if($changed){
        // تحقق من التكرار مع استثناء السجل الحالي
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM gov_fees WHERE fee_title=? AND fee_type=? AND payer=? AND id<>?");
        $stmt->execute([$newData['fee_title'], $newData['fee_type'], $newData['payer'], $id]);
        $exists = $stmt->fetchColumn();

        if($exists > 0){
            $_SESSION['toast'] = [
                'type' => 'warning',
                'msg'  => 'هناك رسم حكومي بنفس العنوان والنوع والدافع موجود بالفعل'
            ];
        } else {
            $stmt = $pdo->prepare("UPDATE gov_fees SET fee_title=?, fee_type=?, fee_amount=?, payer=?, invoice_image=? WHERE id=?");
            $stmt->execute([
                $newData['fee_title'],
                $newData['fee_type'],
                $newData['fee_amount'],
                $newData['payer'],
                $newData['invoice_image'],
                $id
            ]);

            $_SESSION['toast'] = ['type'=>'success','msg'=>'تمت العملية بنجاح'];
        }
    }
}

header('Location: gov_fees.php');
exit;
