<?php 
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id']; 

    // جلب البيانات القديمة
    $o = $pdo->prepare("SELECT * FROM assets WHERE id=?"); 
    $o->execute([$id]); 
    $old = $o->fetch(PDO::FETCH_ASSOC);

    // تجهيز البيانات الجديدة
    $newData = [
        'name' => trim($_POST['name']),
        'type' => trim($_POST['type'] ?? ''),
        'quantity' => (int)($_POST['quantity'] ?? 1),
        'price' => (float)($_POST['price'] ?? 0),
        'payer_name' => trim($_POST['payer_name'] ?? ''),
        'image' => upload_image('image') ?: ($old['image'] ?? null)
    ];

    // تحقق من التكرار (اسم + نوع) بدون احتساب الأصل نفسه
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name=? AND type=? AND id!=?");
    $stmt->execute([$newData['name'], $newData['type'], $id]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك أصل بنفس الاسم والنوع موجود بالفعل'
        ];
    } else {
        // مقارنة البيانات القديمة بالجديدة
        $changed = false;
        foreach ($newData as $key => $value) {
            if ($old[$key] != $value) {
                $changed = true;
                break;
            }
        }

        // نفذ التحديث لو في تغييرات
        if ($changed) {
            $pdo->prepare("UPDATE assets SET name=?, type=?, quantity=?, price=?, payer_name=?, image=? WHERE id=?")
                ->execute([
                    $newData['name'],
                    $newData['type'],
                    $newData['quantity'],
                    $newData['price'],
                    $newData['payer_name'],
                    $newData['image'],
                    $id
                ]);

            $_SESSION['toast'] = [
                'type' => 'success',
                'msg'  => 'تمت العملية بنجاح'
            ];
        }
    }
}

header('Location: '.BASE_URL.'/assets.php');
exit;
