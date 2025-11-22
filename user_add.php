<?php
require __DIR__ . '/config/config.php'; 
require_permission('users.add');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $u = trim($_POST['username']); 
    $p = (string)($_POST['password']); 
    $role_id = (int)($_POST['role_id'] ?? 0);

    // تحقق من القيم المطلوبة
    if ($u === '' || $p === '' || $role_id === 0) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'جميع الحقول مطلوبة.'
        ];
        header('Location: ' . BASE_URL . '/users.php');
        exit;
    }

    try {
        // التحقق من وجود مستخدم بنفس الاسم
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $check->execute([$u]);
        $exists = $check->fetchColumn();

        if ($exists > 0) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'msg'  => 'هناك مستخدم بنفس الاسم موجود بالفعل.'
            ];
        } else {
            // إنشاء المستخدم داخل معاملة (Transaction)
            $pdo->beginTransaction();

            // ⭐ جلب آخر user_id_seq
            $stmtSeq = $pdo->query("SELECT user_id_seq FROM users ORDER BY id DESC LIMIT 1");
            $lastSeq = $stmtSeq->fetchColumn();

            if ($lastSeq) {
                // إزالة "Ad" واخذ الرقم فقط، ثم زود 1
                $num = (int)substr($lastSeq, 2) + 1;
            } else {
                $num = 1; // أول مستخدم
            }

            // تحويل الرقم إلى 4 خانات مع بادئة 0
            $newSeq = 'Ad' . str_pad($num, 4, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role_id, user_id_seq) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $u,
                password_hash($p, PASSWORD_DEFAULT),
                $role_id,
                $newSeq
            ]);

            $pdo->commit();

            $_SESSION['toast'] = [
                'type' => 'success',
                'msg'  => 'تم إنشاء المستخدم بنجاح.'
            ];
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'حدث خطأ أثناء إضافة المستخدم.'
        ];
    }
}

header('Location: ' . BASE_URL . '/users.php');
exit;
