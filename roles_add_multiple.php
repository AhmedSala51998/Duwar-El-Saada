<?php
require __DIR__ . '/config/config.php';
require_permission('roles.add_group');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $roles = $_POST['roles'] ?? [];

    try {
        $pdo->beginTransaction();

        foreach ($roles as $r) {
            $name = trim($r['name'] ?? '');
            $desc = trim($r['description'] ?? '');
            $perms = $r['permissions'] ?? [];

            if ($name === '') continue; // تجاهل الفارغ

            $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            $role_id = $pdo->lastInsertId();

            if (!empty($perms)) {
                $rp = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($perms as $pid) {
                    $rp->execute([$role_id, $pid]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تمت إضافة الأدوار بنجاح.'];
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الإضافة المتعددة.'];
    }
}

header('Location: ' . BASE_URL . '/roles.php');
exit;
