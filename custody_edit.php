<?php
require __DIR__.'/config/config.php';
require_permission('custodies.edit');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $id = (int)$_POST['id'];

    // جلب بيانات العهدة القديمة
    $o = $pdo->prepare("SELECT * FROM custodies WHERE id=?");
    $o->execute([$id]);
    $old = $o->fetch(PDO::FETCH_ASSOC);

    if (!$old) {
        $_SESSION['toast'] = ['type' => 'error', 'msg' => 'العهدة غير موجودة'];
        header('Location: ' . BASE_URL . '/custodies.php');
        exit;
    }

    // جلب بيانات الإدخال الجديدة
    $person_name = trim($_POST['person_name'] ?? '');
    $amount      = (float)($_POST['amount'] ?? 0);
    $taken_at    = trim($_POST['taken_at'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');

    // التحقق من مجموع المبالغ المسحوبة من العهدة
    $stmt = $pdo->prepare("SELECT SUM(amount) AS total_used FROM custody_transactions WHERE custody_id = ?");
    $stmt->execute([$id]);
    $total_used = (float)$stmt->fetchColumn();

    // لو فيه حركات
    if ($total_used > 0 && $amount <= $total_used) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'لا يمكن تعديل مبلغ العهدة ليكون أقل من أو يساوي المبلغ المسحوب منها (' . number_format($total_used, 2) . ' ريال)'
        ];
        header('Location: ' . BASE_URL . '/custodies.php');
        exit;
    }

    // تحديد هل هناك تغييرات فعلًا
    $newData = [
        'person_name' => $person_name,
        'amount'      => $amount,
        'taken_at'    => $taken_at,
        'notes'       => $notes
    ];

    $changed = false;
    foreach ($newData as $k => $v) {
        if (!isset($old[$k]) || $old[$k] != $v) {
            $changed = true;
            break;
        }
    }

    if ($changed) {
        $stmt = $pdo->prepare("UPDATE custodies SET person_name=?, amount=?, main_amount=?, sub_amount=?, taken_at=?, notes=? WHERE id=?");
        $stmt->execute([$person_name, $amount, $amount, $amount, $taken_at, $notes, $id]);
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم تعديل العهدة بنجاح'];
    } else {
        $_SESSION['toast'] = ['type' => 'info', 'msg' => 'لا تغييرات للحفظ'];
    }
}

header('Location: ' . BASE_URL . '/custodies.php');
exit;