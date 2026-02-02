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

        /* === جلب آخر branch_code === */
        $q = $pdo->query("
            SELECT branch_code
            FROM branches
            WHERE branch_code LIKE 'DEB%'
            ORDER BY CAST(SUBSTRING(branch_code, 4) AS UNSIGNED) DESC
            LIMIT 1
        ");

        $lastCode = $q->fetchColumn();
        $counter  = $lastCode ? (int)substr($lastCode, 3) : 0;

        /* === تحضير الإدخال === */
        $stmt = $pdo->prepare("
            INSERT INTO branches
            (branch_code, branch_name, address, phone, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        foreach ($_POST['branch_name'] as $i => $name) {

            $name = trim($name);
            if ($name === '') continue;

            $address = trim($_POST['address'][$i] ?? '');
            $phone   = trim($_POST['phone'][$i] ?? '');

            /* === تحقق من تكرار الاسم === */
            $check = $pdo->prepare("SELECT 1 FROM branches WHERE branch_name = ? LIMIT 1");
            $check->execute([$name]);
            if ($check->fetch()) {
                $errors[] = "اسم الفرع \"$name\" موجود بالفعل";
                continue;
            }

            /* === تحقق من الجوال === */
            if (!preg_match('/^05\d{8}$/', $phone)) {
                $errors[] = "رقم الجوال \"$phone\" للفرع \"$name\" غير صالح";
                continue;
            }

            /* === توليد كود الفرع === */
            $counter++;
            $branchCode = 'DEB' . $counter;

            /* === الإدخال === */
            $stmt->execute([$branchCode, $name, $address, $phone]);
            $insertedCount++;
        }

        $pdo->commit();

        /* === رسائل التوست === */
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

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['toast'] = ['type' => 'danger', 'msg' => '❌ فشل العملية'];
    }
}

header('Location: ' . BASE_URL . '/branches.php');
exit;
