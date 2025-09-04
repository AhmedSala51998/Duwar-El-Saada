<?php
require __DIR__.'/config/config.php'; 
require_role('admin');

if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $u = trim($_POST['username']); 
    $p = (string)($_POST['password']); 
    $r = $_POST['role'] ?? 'staff';

    // التحقق من وجود مستخدم بنفس الاسم
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
    $check->execute([$u]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'هناك مستخدم بنفس الاسم موجود بالفعل'
        ];
    } else {
        // إنشاء المستخدم
        $pdo->prepare("INSERT INTO users(username,password_hash,role) VALUES(?,?,?)")
            ->execute([$u, password_hash($p, PASSWORD_DEFAULT), $r]);

        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تم إنشاء المستخدم'
        ];
    }
}

header('Location: '.BASE_URL.'/users.php');
exit;
