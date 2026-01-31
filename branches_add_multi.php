<?php
require __DIR__ . '/config/config.php';
require_permission('branches.add_group'); // صلاحية الإضافة

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    // التأكد من وجود بيانات فروع
    if (empty($_POST['branch_name']) || !is_array($_POST['branch_name'])) {
        $_SESSION['toast'] = ['type'=>'warning','msg'=>'لم يتم إدخال أي فرع'];
        header('Location: ' . BASE_URL . '/branches.php');
        exit;
    }

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
                $pdo->rollBack();
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => "اسم الفرع \"$name\" موجود بالفعل"
                ];
                header('Location: ' . BASE_URL . '/branches.php');
                exit;
            }

            // تحقق من رقم الجوال السعودي
            if (!preg_match('/^05\d{8}$/', $phone)) {
                $pdo->rollBack();
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => "رقم الجوال \"$phone\" للفرع \"$name\" غير صالح"
                ];
                header('Location: ' . BASE_URL . '/branches.php');
                exit;
            }

            // إدخال البيانات
            $stmt->execute([$name, $address, $phone]);
        }

        $pdo->commit();
        $_SESSION['toast'] = ['type' => 'success', 'msg' => '✅ تم حفظ الفروع بنجاح'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => '❌ فشل العملية: ' . $e->getMessage()];
    }
}

header('Location: ' . BASE_URL . '/branches.php');
exit;