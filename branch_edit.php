<?php
require __DIR__.'/config/config.php';
require_permission('branches.edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['_csrf'] ?? '')) {
    header('Location: branches.php');
    exit;
}

$id    = (int)$_POST['id'];
$name  = trim($_POST['branch_name']);
$addr  = trim($_POST['address']);
$phone = trim($_POST['phone']);

if (!preg_match('/^05\d{8}$/', $phone)) {
    $_SESSION['toast'] = ['type'=>'danger','msg'=>'رقم الجوال غير صحيح'];
    header('Location: branches.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // قفل الصف
    $old = $pdo->prepare("SELECT * FROM branches WHERE id=? FOR UPDATE");
    $old->execute([$id]);
    $oldData = $old->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'الفرع غير موجود'];
        header('Location: branches.php');
        exit;
    }

    // تحقق من وجود تغيير فعلي
    if (
        $oldData['branch_name'] === $name &&
        $oldData['address']     === $addr &&
        $oldData['phone']       === $phone
    ) {
        $pdo->rollBack();
        $_SESSION['toast'] = ['type'=>'info','msg'=>'لم يتم تعديل أي بيانات'];
        header('Location: branches.php');
        exit;
    }

    // تنفيذ التحديث
    $upd = $pdo->prepare("
        UPDATE branches
        SET branch_name = ?, address = ?, phone = ?
        WHERE id = ?
    ");
    $upd->execute([$name, $addr, $phone, $id]);

    $pdo->commit();

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تم تعديل الفرع بنجاح'];

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['toast'] = ['type'=>'danger','msg'=>'خطأ أثناء التعديل'];
}

header('Location: branches.php');
exit;