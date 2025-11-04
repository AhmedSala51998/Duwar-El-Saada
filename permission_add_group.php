<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/config/config.php';
require_permission('permissions.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codes = $_POST['codes'] ?? [];
    $labels = $_POST['labels'] ?? [];
    $descriptions = $_POST['descriptions'] ?? [];
    $csrf = $_POST['_csrf'] ?? '';

    /*if (!verify_csrf($csrf)) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'رمز الأمان غير صالح.'];
        header('Location: ' . BASE_URL . '/permissions.php');
    }*/

    $count = 0;
    $stmt = $pdo->prepare("INSERT INTO permissions (code, label, description) VALUES (?, ?, ?)");
    foreach ($codes as $i => $code) {
        $label = trim($labels[$i] ?? '');
        $desc  = trim($descriptions[$i] ?? '');
        if ($code && $label) {
            $stmt->execute([$code, $label, $desc]);
            $count++;
        }
    }

    $_SESSION['toast'] = ['type'=>'success','msg'=>"تمت إضافة $count صلاحية بنجاح."];
    header('Location: ' . BASE_URL . '/permissions.php');
}
?>
