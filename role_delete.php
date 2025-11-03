<?php
require __DIR__ . '/config/config.php';
require_role('admin');

// جلب معرف الدور
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'لم يتم تحديد الدور المطلوب.'];
    redirect('roles');
}

try {
    $pdo->beginTransaction();

    // 🔸 تحديث المستخدمين اللي عندهم الدور ده → نخلي role_id = NULL
    $stmt = $pdo->prepare("UPDATE users SET role_id = NULL WHERE role_id = ?");
    $stmt->execute([$id]);

    // 🔸 حذف الصلاحيات المرتبطة بالدور من جدول العلاقة
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$id]);

    // 🔸 حذف الدور نفسه
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم حذف الدور بنجاح.'];
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()];
}

redirect('roles');
