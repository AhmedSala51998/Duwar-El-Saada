<?php
require __DIR__ . '/config/config.php';
require_permission('systems_settings.delete');

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg' => 'لم يتم تحديد الإعداد.'
    ];
    header('Location: ' . BASE_URL . '/settings.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // جلب البيانات قبل الحذف لحذف الصور القديمة
    $stmt = $pdo->prepare("SELECT main_logo, secondary_logo FROM system_settings WHERE id = ?");
    $stmt->execute([$id]);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($setting) {
        // حذف الصور من السيرفر إذا كانت موجودة
        if (!empty($setting['main_logo']) && file_exists($setting['main_logo'])) {
            unlink($setting['main_logo']);
        }
        if (!empty($setting['secondary_logo']) && file_exists($setting['secondary_logo'])) {
            unlink($setting['secondary_logo']);
        }

        // حذف السجل من قاعدة البيانات
        $stmtDel = $pdo->prepare("DELETE FROM system_settings WHERE id = ?");
        $stmtDel->execute([$id]);
    }

    $pdo->commit();

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg' => 'تم حذف الإعداد بنجاح.'
    ];

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg' => 'حدث خطأ أثناء حذف الإعداد.'
    ];
}

header('Location: ' . BASE_URL . '/settings.php');
exit;
