<?php
require __DIR__ . '/config/config.php';
require_role('admin');
check_csrf();

$id = (int)($_POST['id'] ?? 0);
$code = trim($_POST['code'] ?? '');
$label = trim($_POST['label'] ?? '');
$desc = trim($_POST['description'] ?? '');

if (!$id || $code === '' || $label === '') {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'جميع الحقول مطلوبة.'];
    redirect('permissions');
}

try {
    $stmt = $pdo->prepare("UPDATE permissions SET code=?, label=?, description=? WHERE id=?");
    $stmt->execute([$code, $label, $desc, $id]);
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم تحديث الصلاحية بنجاح.'];
} catch (PDOException $e) {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء التحديث.'];
}

redirect('permissions');
