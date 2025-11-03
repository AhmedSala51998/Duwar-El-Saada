<?php
require __DIR__ . '/config/config.php';
require_permission('permissions.edit');
// check_csrf();

$id    = (int)($_POST['id'] ?? 0);
$code  = trim($_POST['code'] ?? '');
$label = trim($_POST['label'] ?? '');
$desc  = trim($_POST['description'] ?? 'NULL');

if (!$id || $code === '' || $label === '') {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'جميع الحقول مطلوبة.'];
    header('Location: ' . BASE_URL . '/permissions.php');
    exit;
}

try {
    // 🧱 بدء معاملة قاعدة البيانات
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE permissions SET code = ?, label = ?, description = ? WHERE id = ?");
    $stmt->execute([$code, $label, $desc, $id]);

    // ✅ تأكيد التغييرات
    $pdo->commit();

    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم تحديث الصلاحية بنجاح.'];
} catch (PDOException $e) {
    // ❌ في حال حدوث خطأ يتم التراجع عن التحديث
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء التحديث.'];
}

header('Location: ' . BASE_URL . '/permissions.php');
exit;
