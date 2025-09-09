<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];

    // جلب البيانات القديمة
    $old = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    // تجهيز البيانات الجديدة
    $newData = [
        'name'          => trim($_POST['name']),
        'quantity'      => (float)($_POST['quantity'] ?? 0),
        'unit'          => $_POST['unit'] ?? '',
        'price'         => (float)($_POST['price'] ?? 0),
        'product_image' => upload_image('product_image') ?: ($oldData['product_image'] ?? null),
        'invoice_image' => upload_image('invoice_image') ?: ($oldData['invoice_image'] ?? null),
        'payer_name'    => trim($_POST['payer_name'] ?? '')
    ];

    // تحقق من التكرار مع السجلات الأخرى (باستثناء السجل الحالي)
    $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE name=? AND unit=? AND payer_name=? AND id<>?");
    $check->execute([$newData['name'], $newData['unit'], $newData['payer_name'], $id]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك عملية شراء بنفس الاسم، الوحدة والدافع موجودة بالفعل'
        ];
    } else {
        // التحقق من وجود تغييرات
        $changed = false;
        foreach ($newData as $key => $value) {
            if ($oldData[$key] != $value) {
                $changed = true;
                break;
            }
        }

        // نفذ التحديث فقط لو في تغييرات
        if ($changed) {
            $pdo->prepare("
                UPDATE purchases 
                SET name=?, quantity=?, unit=?, price=?, product_image=?, invoice_image=?, payer_name=? 
                WHERE id=?
            ")->execute([
                $newData['name'],
                $newData['quantity'],
                $newData['unit'],
                $newData['price'],
                $newData['product_image'],
                $newData['invoice_image'],
                $newData['payer_name'],
                $id
            ]);

            $_SESSION['toast'] = [
                'type' => 'success',
                'msg'  => 'تم تعديل العملية بنجاح'
            ];
        }
    }
}

header('Location: ' . BASE_URL . '/purchases.php');
exit;
