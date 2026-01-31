<?php
require __DIR__.'/config/config.php';
require_permission('branches.add');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['_csrf'] ?? '')) {
    header('Location: branches.php');
    exit;
}

$name  = trim($_POST['branch_name']);
$addr  = trim($_POST['address']);
$phone = trim($_POST['phone']);

if (!preg_match('/^05\d{8}$/', $phone)) {
    $_SESSION['toast']=['type'=>'danger','msg'=>'رقم الجوال غير صحيح'];
    header('Location: branches.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // منع تكرار اسم الفرع
    $chk = $pdo->prepare("SELECT 1 FROM branches WHERE branch_name=? LIMIT 1");
    $chk->execute([$name]);

    if ($chk->fetch()) {
        $pdo->rollBack();
        $_SESSION['toast']=['type'=>'warning','msg'=>'اسم الفرع موجود مسبقًا'];
        header('Location: branches.php');
        exit;
    }

    $ins = $pdo->prepare("
        INSERT INTO branches (branch_name, address, phone)
        VALUES (?, ?, ?)
    ");
    $ins->execute([$name, $addr, $phone]);

    $pdo->commit();

    $_SESSION['toast']=['type'=>'success','msg'=>'تم إضافة الفرع'];

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['toast']=['type'=>'danger','msg'=>'خطأ أثناء الإضافة'];
}

header('Location: branches.php');
exit;
