<?php
require __DIR__ . '/config/config.php';
require_permission('users.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $usernames = $_POST['username'] ?? [];
    $passwords = $_POST['password'] ?? [];
    $roles     = $_POST['role_id'] ?? [];

    // لو مفيش ولا صف
    if (count($usernames) === 0) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'يجب إضافة صف واحد على الأقل.'
        ];
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        $insert = $pdo->prepare("
            INSERT INTO users (username, password_hash, role_id)
            VALUES (?, ?, ?)
        ");

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");

        $rowsAdded = 0;
        $duplicates = [];

        foreach ($usernames as $i => $u) {

            $u = trim($u);
            $p = trim($passwords[$i] ?? '');
            $r = (int)($roles[$i] ?? 0);

            // تخطي الصف لو ناقص
            if ($u === '' || $p === '' || $r === 0) {
                continue;
            }

            // فحص التكرار
            $check->execute([$u]);
            if ($check->fetchColumn() > 0) {
                $duplicates[] = $u;
                continue;
            }

            // إدراج
            $insert->execute([
                $u,
                password_hash($p, PASSWORD_DEFAULT),
                $r
            ]);

            $rowsAdded++;
        }

        $pdo->commit();

        // تحديد رسالة الواجهة
        if ($rowsAdded > 0 && empty($duplicates)) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'msg'  => "تم إضافة $rowsAdded مستخدم بنجاح."
            ];
        } elseif ($rowsAdded > 0 && !empty($duplicates)) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'msg'  => "تم إضافة $rowsAdded مستخدم، لكن توجد أسماء مكررة لم تُضف: " . implode(", ", $duplicates)
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'msg'  => "لم يتم إضافة أي مستخدم بسبب وجود بيانات ناقصة أو أسماء مكررة."
            ];
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'حدث خطأ أثناء إضافة المستخدمين.'
        ];
    }
}

header('Location: ' . BASE_URL . '/users.php');
exit;
