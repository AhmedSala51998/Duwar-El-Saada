<?php

require __DIR__.'/config/config.php';
require_permission('dhimam.add');

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !csrf_validate($_POST['_csrf'] ?? '')
) {
    http_response_code(403);
    exit('طلب غير مصرح به');
}

require __DIR__.'/dhimam_validate.php';

try {
    [$name, $amount, $description, $date] = dhimam_input();

    $stmt = $pdo->prepare(
        'INSERT INTO dhimam
         (person_name, amount, description, received_at)
         VALUES (?, ?, ?, ?)'
    );

    $stmt->execute([$name, $amount, $description, $date]);

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تمت إضافة الذمة بنجاح'
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
        'msg'  => 'تعذر حفظ الذمة'
    ];
}

header('Location: '.BASE_URL.'/dhimam.php');
exit;