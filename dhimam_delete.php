<?php

require __DIR__.'/config/config.php';
require_permission('dhimam.delete');

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !csrf_validate($_POST['_csrf'] ?? '')
) {
    http_response_code(403);
    exit('طلب غير مصرح به');
}

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id) {
    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg'  => 'رقم الذمة غير صحيح'
    ];
} else {
    try {
        $stmt = $pdo->prepare('DELETE FROM dhimam WHERE id = ?');
        $stmt->execute([$id]);

        $_SESSION['toast'] = $stmt->rowCount()
            ? ['type' => 'success', 'msg' => 'تم حذف الذمة']
            : ['type' => 'warning', 'msg' => 'الذمة غير موجودة'];
    } catch (PDOException $e) {
        error_log($e->getMessage());

        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'تعذر حذف الذمة'
        ];
    }
}

header('Location: '.BASE_URL.'/dhimam.php');
exit;