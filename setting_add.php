<?php
require __DIR__ . '/config/config.php';
require_permission('systems_settings.add');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $text1 = trim($_POST['text1']);
    $text2 = trim($_POST['text2']);
    $footer_text = trim($_POST['footer_text']);

    // تحقق من الحقول المطلوبة
    if ($text1 === '' || $text2 === '' || $footer_text === '') {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'جميع الحقول مطلوبة.'
        ];
        header('Location: ' . BASE_URL . '/settings.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // رفع الشعار الرئيسي
        $main_logo = null;
        if (!empty($_FILES['main_logo']['tmp_name'])) {
            $ext = pathinfo($_FILES['main_logo']['name'], PATHINFO_EXTENSION);
            $main_logo = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['main_logo']['tmp_name'], $main_logo);
        }

        // رفع الشعار الثانوي
        $secondary_logo = null;
        if (!empty($_FILES['secondary_logo']['tmp_name'])) {
            $ext = pathinfo($_FILES['secondary_logo']['name'], PATHINFO_EXTENSION);
            $secondary_logo = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['secondary_logo']['tmp_name'], $secondary_logo);
        }

        // إدخال البيانات في قاعدة البيانات
        $stmt = $pdo->prepare("
            INSERT INTO system_settings 
            (main_logo, secondary_logo, text1, text2, footer_text) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$main_logo, $secondary_logo, $text1, $text2, $footer_text]);

        $pdo->commit();

        $_SESSION['toast'] = [
            'type' => 'success',
            'msg'  => 'تم إضافة الإعداد بنجاح.'
        ];

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'حدث خطأ أثناء إضافة الإعداد.'
        ];
    }
}

header('Location: ' . BASE_URL . '/settings.php');
exit;
