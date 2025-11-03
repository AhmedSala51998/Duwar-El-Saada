<?php
require __DIR__ . '/config/config.php';
require_role('admin');
//check_csrf();

$code = trim($_POST['code'] ?? '');
$label = trim($_POST['label'] ?? '');
$desc = trim($_POST['description'] ?? '');

if ($code === '' || $label === '') {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'الرجاء إدخال الكود والاسم.'];
    redirect('permissions');
}

try {
    $stmt = $pdo->prepare("INSERT INTO permissions (code, label, description) VALUES (?, ?, ?)");
    $stmt->execute([$code, $label, $desc]);
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تمت إضافة الصلاحية بنجاح.'];
} catch (PDOException $e) {
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'حدث خطأ أثناء الإضافة.'];
}

redirect('permissions');
