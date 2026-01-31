<?php
require __DIR__ . '/config/config.php';
require_permission('branches.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    if (empty($_POST['branch_name']) || !is_array($_POST['branch_name'])) {
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'لم يتم إدخال أي فرع'];
        header('Location: ' . BASE_URL . '/branches.php');
        exit;
    }

    $errors = [];
    $insertedCount = 0;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO branches
            (branch_name, address, phone, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        foreach ($_POST['branch_name'] as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;

            $address = trim($_POST['address'][$i] ?? '');
            $phone   = trim($_POST['phone'][$i] ?? '');

            // تحقق من الاسم: لا يتكرر
            $check = $pdo->prepare("SELECT id FROM branches WHERE branch_name = ? LIMIT 1");
            $check->execute([$name]);
            if ($check->fetch()) {
                $errors[] = "اسم الفرع \"$name\" موجود بالفعل";
                continue; // نكمل الصفوف الأخرى
            }

            // تحقق من رقم الجوال السعودي
            if (!preg_match('/^05\d{8}$/', $phone)) {
                $errors[] = "رقم الجوال \"$phone\" للفرع \"$name\" غير صالح";
                continue; // نكمل الصفوف الأخرى
            }

            // إدخال البيانات
            $stmt->execute([$name, $address, $phone]);
            $insertedCount++;
        }

        $pdo->commit();

        // رسالة توست بناءً على النتائج
        $msg = '';
        if ($insertedCount > 0) {
            $msg .= "✅ تم حفظ $insertedCount فرع بنجاح.";
        }
        if (!empty($errors)) {
            $msg .= "<br>⚠️ لم يتم حفظ بعض الصفوف:<br>" . implode('<br>', $errors);
            $_SESSION['toast'] = ['type' => 'warning', 'msg' => $msg];
        } else {
            $_SESSION['toast'] = ['type' => 'success', 'msg' => $msg];
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => '❌ فشل العملية: ' . $e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/branches.php');
exit;
