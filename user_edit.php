<?php
require __DIR__.'/config/config.php'; 
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];
    $u = trim($_POST['username']);
    $r = $_POST['role'] ?? 'staff';
    $pwd = (string)($_POST['password'] ?? '');

    // التحقق إذا كان هناك مستخدم بنفس الاسم موجود عند شخص آخر
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=? AND id<>?");
    $check->execute([$u, $id]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg' => 'هناك مستخدم بنفس الاسم موجود بالفعل'
        ];
    } else {
        // جلب البيانات القديمة للمستخدم
        $old = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $old->execute([$id]);
        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        // تجهيز البيانات الجديدة
        $newData = [
            'username' => $u,
            'role' => $r,
            'password_hash' => $pwd ? password_hash($pwd, PASSWORD_DEFAULT) : $oldData['password_hash']
        ];

        // التحقق من وجود تغييرات
        $changed = false;
        foreach ($newData as $key => $value) {
            if ($oldData[$key] != $value) {
                $changed = true;
                break;
            }
        }

        // تنفيذ التحديث فقط إذا كان هناك تغييرات فعلية
        if ($changed) {
            $pdo->prepare("UPDATE users SET username=?, role=?, password_hash=? WHERE id=?")
                ->execute([$newData['username'], $newData['role'], $newData['password_hash'], $id]);

            $_SESSION['toast'] = [
                'type' => 'success',
                'msg' => 'تم تحديث المستخدم بنجاح'
            ];
        }
    }
}

header('Location: '.BASE_URL.'/users.php');
exit;
