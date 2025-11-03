<?php
require __DIR__ . '/config/config.php'; 
require_permission('users.edit');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];
    $u = trim($_POST['username']);
    $role_id = (int)($_POST['role_id'] ?? 0);
    $pwd = (string)($_POST['password'] ?? '');

    // التحقق من القيم المطلوبة
    if ($id <= 0 || $u === '' || $role_id === 0) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'جميع الحقول مطلوبة.'
        ];
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }

    try {
        // التحقق إذا كان هناك مستخدم بنفس الاسم عند شخص آخر
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?");
        $check->execute([$u, $id]);
        $exists = $check->fetchColumn();

        if ($exists > 0) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'msg'  => 'هناك مستخدم بنفس الاسم موجود بالفعل.'
            ];
        } else {
            // جلب البيانات القديمة
            $old = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $old->execute([$id]);
            $oldData = $old->fetch(PDO::FETCH_ASSOC);

            if (!$oldData) {
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => 'المستخدم غير موجود.'
                ];
                header('Location: ' . BASE_URL . '/users.php');
                exit;
            }

            // تجهيز البيانات الجديدة
            $newData = [
                'username' => $u,
                'role_id' => $role_id,
                'password_hash' => $pwd ? password_hash($pwd, PASSWORD_DEFAULT) : $oldData['password_hash']
            ];

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
                $pdo->beginTransaction();

                $update = $pdo->prepare("UPDATE users SET username = ?, role_id = ?, password_hash = ? WHERE id = ?");
                $update->execute([$newData['username'], $newData['role_id'], $newData['password_hash'], $id]);

                $pdo->commit();

                $_SESSION['toast'] = [
                    'type' => 'success',
                    'msg'  => 'تم تحديث المستخدم بنجاح.'
                ];
            } else {
                $_SESSION['toast'] = [
                    'type' => 'info',
                    'msg'  => 'لم يتم إجراء أي تغييرات.'
                ];
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'حدث خطأ أثناء التحديث.'
        ];
    }
}

header('Location: ' . BASE_URL . '/users.php');
exit;