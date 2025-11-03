<?php
require __DIR__ . '/config/config.php';
require_permission('roles.edit');
//check_csrf();

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');
$perms = $_POST['permissions'] ?? [];

if (!$id || $name === '') {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'جميع الحقول مطلوبة.'];
    header('Location: '.BASE_URL.'/roles.php');
}

try {
    $pdo->beginTransaction();

    $pdo->prepare("UPDATE roles SET name=?, description=? WHERE id=?")->execute([$name, $desc, $id]);

    // احذف الصلاحيات القديمة
    $pdo->prepare("DELETE FROM role_permissions WHERE role_id=?")->execute([$id]);

    // أضف الصلاحيات الجديدة
    if (!empty($perms)) {
        $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($perms as $pid) {
            $stmt->execute([$id, $pid]);
        }
    }

    $pdo->commit();
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم تحديث الدور وصلاحياته بنجاح.'];
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء التحديث.'];
}

header('Location: '.BASE_URL.'/roles.php');
