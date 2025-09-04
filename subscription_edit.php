<?php
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    // تجهيز البيانات الجديدة
    $newData = [
        'service_name'      => trim($_POST['service_name']),
        'subscribers'       => trim($_POST['subscribers']),
        'subscription_type' => $_POST['subscription_type'] ?? '',
        'service_price'     => (float)($_POST['service_price'] ?? 0),
        'payer'             => $_POST['payer'] ?? '',
        'invoice_image'     => upload_image('invoice_image')
    ];

    // التحقق من وجود سجل مطابق مسبقًا (باستثناء السجل الحالي)
    $check = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE service_name=? AND subscribers=? AND subscription_type=? AND payer=? AND id<>?");
    $check->execute([$newData['service_name'], $newData['subscribers'], $newData['subscription_type'], $newData['payer'], $id]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك اشتراك بنفس المواصفات موجود بالفعل'
        ];
    } else {
        // جلب البيانات القديمة لتحديد التغييرات
        $old = $pdo->prepare("SELECT * FROM subscriptions WHERE id=?");
        $old->execute([$id]);
        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        // استبدال الصورة القديمة إذا لم يتم رفع صورة جديدة
        if (!$newData['invoice_image']) {
            $newData['invoice_image'] = $oldData['invoice_image'] ?? null;
        }

        // التحقق من وجود تغييرات فعلية
        $changed = false;
        foreach ($newData as $key => $value) {
            if ($oldData[$key] != $value) {
                $changed = true;
                break;
            }
        }

        // تنفيذ التحديث فقط إذا كان هناك تغييرات
        if ($changed) {
            $stmt = $pdo->prepare("
                UPDATE subscriptions 
                SET service_name=?, subscribers=?, subscription_type=?, service_price=?, payer=?, invoice_image=? 
                WHERE id=?
            ");
            $stmt->execute([
                $newData['service_name'],
                $newData['subscribers'],
                $newData['subscription_type'],
                $newData['service_price'],
                $newData['payer'],
                $newData['invoice_image'],
                $id
            ]);

            $_SESSION['toast'] = [
                'type' => 'success',
                'msg'  => 'تمت العملية بنجاح'
            ];
        }
    }
}

header('Location: subscriptions.php');
exit;
