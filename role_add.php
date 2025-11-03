<?php
require __DIR__ . '/config/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_role('admin');
check_csrf();


$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');
$perms = $_POST['permissions'] ?? [];

if ($name === '') {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'اسم الدور مطلوب.'];
    redirect('roles');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);
    $role_id = $pdo->lastInsertId();

    if (!empty($perms)) {
        $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($perms as $pid) {
            $stmt->execute([$role_id, $pid]);
        }
    }

    $pdo->commit();
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم إنشاء الدور بنجاح مع صلاحياته.'];
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الإضافة.'];
}

redirect('roles');
