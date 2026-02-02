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

    /* === منع تكرار اسم الفرع === */
    $chk = $pdo->prepare("SELECT 1 FROM branches WHERE branch_name=? LIMIT 1");
    $chk->execute([$name]);

    if ($chk->fetch()) {
        $pdo->rollBack();
        $_SESSION['toast']=['type'=>'warning','msg'=>'اسم الفرع موجود مسبقًا'];
        header('Location: branches.php');
        exit;
    }

    /* === توليد كود الفرع DEB1, DEB2 ... === */
    $q = $pdo->query("
        SELECT branch_code
        FROM branches
        WHERE branch_code LIKE 'DEB%'
        ORDER BY CAST(SUBSTRING(branch_code, 4) AS UNSIGNED) DESC
        LIMIT 1
    ");

    $lastCode = $q->fetchColumn();

    if ($lastCode) {
        $lastNum   = (int)substr($lastCode, 3); // بعد DEB
        $newNum    = $lastNum + 1;
    } else {
        $newNum = 1;
    }

    $branchCode = 'DEB' . $newNum;

    /* === الإدخال === */
    $ins = $pdo->prepare("
        INSERT INTO branches (branch_code, branch_name, address, phone)
        VALUES (?, ?, ?, ?)
    ");
    $ins->execute([$branchCode, $name, $addr, $phone]);

    $pdo->commit();

    $_SESSION['toast']=[
        'type'=>'success',
        'msg'=>"تم إضافة الفرع ({$branchCode})"
    ];

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['toast']=['type'=>'danger','msg'=>'خطأ أثناء الإضافة'];
}

header('Location: branches.php');
exit;
