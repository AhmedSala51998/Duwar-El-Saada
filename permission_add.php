<?php
require __DIR__ . '/config/config.php';
require_permission('permissions.add');
// check_csrf();

$code  = trim($_POST['code'] ?? '');
$label = trim($_POST['label'] ?? '');
$desc  = trim($_POST['description'] ?? 'NULL');

if ($code === '' || $label === '') {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'الرجاء إدخال الكود والاسم.'];
    header('Location: ' . BASE_URL . '/permissions.php');
    exit;
}

try {
    // 🧱 بدء معاملة قاعدة البيانات
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO permissions (code, label, description) VALUES (?, ?, ?)");
    $stmt->execute([$code, $label, $desc]);

    // ✅ حفظ التغييرات في قاعدة البيانات
    $pdo->commit();

    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تمت إضافة الصلاحية بنجاح.'];
} catch (PDOException $e) {
    // ❌ في حالة الخطأ، نلغي التغييرات
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الإضافة.'];
}

header('Location: ' . BASE_URL . '/permissions.php');
exit;
