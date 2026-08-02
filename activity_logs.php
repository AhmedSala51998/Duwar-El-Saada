<?php
require __DIR__.'/partials/header.php';
require_permission('logs.view');

$kw = trim($_GET['kw'] ?? '');

$sql = "
SELECT *
FROM activity_logs
WHERE
    username LIKE ?
    OR module LIKE ?
    OR action LIKE ?
    OR description LIKE ?
ORDER BY id DESC
LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    "%$kw%",
    "%$kw%",
    "%$kw%",
    "%$kw%"
]);

$rows = $stmt->fetchAll();
?>

<style>
.log-card{
    border:none;
    border-radius:15px;
    overflow:hidden;
}

.log-header{
    background:#ff6a00;
    color:#fff;
}

.badge-action{
    font-size:.8rem;
    padding:.45rem .8rem;
}

.action-add{
    background:#198754;
}

.action-edit{
    background:#ffc107;
    color:#000;
}

.action-delete{
    background:#dc3545;
}

.action-login{
    background:#0d6efd;
}

.action-logout{
    background:#6c757d;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h3 class="mb-0">
        <i class="bi bi-clock-history text-warning"></i>
        سجل العمليات
    </h3>

    <form method="get" class="d-flex gap-2">

        <input
            type="text"
            name="kw"
            class="form-control"
            placeholder="بحث..."
            value="<?= esc($kw) ?>"
        >

        <button class="btn btn-warning text-white">
            <i class="bi bi-search"></i>
        </button>

    </form>

</div>

<div class="card shadow-sm log-card">

    <div class="card-header log-header">

        <div class="fw-bold">
            آخر العمليات داخل النظام
        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

            <tr>

                <th>#</th>
                <th>المستخدم</th>
                <th>العملية</th>
                <th>القسم</th>
                <th>رقم السجل</th>
                <th>الوصف</th>
                <th>IP</th>
                <th>التاريخ</th>

            </tr>

            </thead>

            <tbody>

            <?php foreach($rows as $r): ?>

                <?php

                $class='bg-secondary';

                switch($r['action']){

                    case 'add':
                        $class='action-add';
                        break;

                    case 'edit':
                        $class='action-edit';
                        break;

                    case 'delete':
                        $class='action-delete';
                        break;

                    case 'login':
                        $class='action-login';
                        break;

                    case 'logout':
                        $class='action-logout';
                        break;
                }

                ?>

                <tr>

                    <td><?= $r['id'] ?></td>

                    <td>
                        <strong>
                            <?= esc($r['username']) ?>
                        </strong>
                    </td>

                    <td>

                        <span class="badge <?= $class ?> badge-action">

                            <?= esc($r['action']) ?>

                        </span>

                    </td>

                    <td>

                        <?= esc($r['module']) ?>

                    </td>

                    <td>

                        <?= $r['record_id'] ?: '-' ?>

                    </td>

                    <td>

                        <?= esc($r['description']) ?>

                    </td>

                    <td>

                        <?= esc($r['ip_address']) ?>

                    </td>

                    <td>

                        <?= esc($r['created_at']) ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require __DIR__.'/partials/footer.php'; ?>