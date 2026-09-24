<?php

require __DIR__.'/config/config.php';
require_permission('dhimam.edit');

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !csrf_validate($_POST['_csrf'] ?? '')
) {
    http_response_code(403);
    exit('طلب غير مصرح به');
}

require __DIR__.'/dhimam_validate.php';

try {
    $id = filter_var(
        $_POST['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$id) {
        throw new InvalidArgumentException('رقم الذمة غير صحيح');
    }

    [$name, $amount, $description, $date] = dhimam_input();

    $stmt = $pdo->prepare(
        'UPDATE dhimam
         SET person_name = ?,
             amount = ?,
             description = ?,
             received_at = ?
         WHERE id = ?'
    );

    $stmt->execute([
        $name,
        $amount,
        $description,
        $date,
        $id
    ]);

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تم حفظ التعديل'
    ];
} catch (InvalidArgumentException $e) {
    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg'  => $e->getMessage()
    ];
} catch (PDOException $e) {
    error_log($e->getMessage());

    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg'  => 'تعذر تعديل الذمة'
    ];
}

header('Location: '.BASE_URL.'/dhimam.php');
exit;