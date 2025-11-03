<?php
require __DIR__ . '/config/config.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('permissions');

try {
    $pdo->beginTransaction();

    // احذف العلاقات مع الأدوار أولاً
    $pdo->prepare("DELETE FROM role_permissions WHERE permission_id=?")->execute([$id]);

    // ثم احذف الصلاحية نفسها
    $pdo->prepare("DELETE FROM permissions WHERE id=?")->execute([$id]);

    $pdo->commit();
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم حذف الصلاحية بنجاح.'];
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الحذف.'];
}

redirect('permissions');
