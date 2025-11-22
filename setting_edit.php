<?php
require __DIR__ . '/config/config.php';
require_permission('systems_settings.edit');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $text1 = trim($_POST['text1']);
    $text2 = trim($_POST['text2']);
    $footer_text = trim($_POST['footer_text']);

    // تحقق من الحقول المطلوبة
    if ($id === 0 || $text1 === '' || $text2 === '' || $footer_text === '') {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'جميع الحقول مطلوبة.'
        ];
        header('Location: ' . BASE_URL . '/settings.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // جلب البيانات الحالية للاحتفاظ بالصور القديمة إذا لم يتم رفع جديدة
        $stmtCurrent = $pdo->prepare("SELECT main_logo, secondary_logo FROM system_settings WHERE id = ?");
        $stmtCurrent->execute([$id]);
        $current = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

        // رفع الشعار الرئيسي إذا تم اختيار ملف جديد
        $main_logo = $current['main_logo'];
        if (!empty($_FILES['main_logo']['tmp_name'])) {
            $ext = pathinfo($_FILES['main_logo']['name'], PATHINFO_EXTENSION);
            $main_logo = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['main_logo']['tmp_name'], $main_logo);
        }

        // رفع الشعار الثانوي إذا تم اختيار ملف جديد
        $secondary_logo = $current['secondary_logo'];
        if (!empty($_FILES['secondary_logo']['tmp_name'])) {
            $ext = pathinfo($_FILES['secondary_logo']['name'], PATHINFO_EXTENSION);
            $secondary_logo = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['secondary_logo']['tmp_name'], $secondary_logo);
        }

        // تحديث البيانات في قاعدة البيانات
        $stmt = $pdo->prepare("
            UPDATE system_settings 
            SET main_logo = ?, 
                secondary_logo = ?, 
                text1 = ?, 
                text2 = ?, 
                footer_text = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$main_logo, $secondary_logo, $text1, $text2, $footer_text, $id]);

        $pdo->commit();

        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تم تعديل الإعدادات بنجاح.'
        ];

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'حدث خطأ أثناء تعديل الإعدادات.'
        ];
    }
}

header('Location: ' . BASE_URL . '/settings.php');
exit;
