<?php
require __DIR__ . '/config/config.php';
require_permission('branches.delete');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: branches.php');
    exit;
}

try {
    // 🔐 بدء المعاملة
    $pdo->beginTransaction();

    // تأكيد إن الفرع موجود
    $exists = $pdo->prepare("SELECT id FROM branches WHERE id = ? FOR UPDATE");
    $exists->execute([$id]);

    if (!$exists->fetch()) {
        $pdo->rollBack();
        $_SESSION['toast'] = [
            'type' => 'danger',
            'msg'  => 'الفرع غير موجود.'
        ];
        header('Location: branches.php');
        exit;
    }

    // الجداول المرتبطة
    $relations = [
        'expenses',
        'purchases',
        'assets',
        'custodies',
        'orders_purchases'
    ];

    foreach ($relations as $table) {
        $check = $pdo->prepare(
            "SELECT 1 FROM {$table} WHERE branch_id = ? LIMIT 1 FOR UPDATE"
        );
        $check->execute([$id]);

        if ($check->fetch()) {
            // ❌ مرتبط → Rollback
            $pdo->rollBack();
            $_SESSION['toast'] = [
                'type' => 'warning',
                'msg'  => 'لا يمكن حذف الفرع لأنه مرتبط ببيانات مالية.'
            ];
            header('Location: branches.php');
            exit;
        }
    }

    // ✅ آمن للحذف
    $del = $pdo->prepare("DELETE FROM branches WHERE id = ?");
    $del->execute([$id]);

    require_once __DIR__.'/libs/activity_log.php';

    add_activity_log(
        $pdo,
        'delete',
        'branches',
        $id,
        "حذف فرع"
    );

    // 🎯 تأكيد العملية
    $pdo->commit();

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تم حذف الفرع بنجاح.'
    ];

} catch (Throwable $e) {
    // 💣 أي خطأ = Rollback
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg'  => 'خطأ غير متوقع أثناء الحذف.'
    ];
}

header('Location: branches.php');
exit;