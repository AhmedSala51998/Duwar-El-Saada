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
/* ============================
   DARK MODE - LOGS PAGE
============================ */

body.dark-mode .log-card{
    background:#1b1b1b !important;
    border:1px solid #333 !important;
    box-shadow:0 0 15px rgba(0,0,0,.4) !important;
}

body.dark-mode .log-header{
    background:linear-gradient(135deg,#ff6a00,#ff8c42) !important;
    color:#fff !important;
}

body.dark-mode .log-header *{
    color:#fff !important;
}

/* نموذج البحث */
body.dark-mode input.form-control{
    background:#232323 !important;
    border:1px solid #444 !important;
    color:#fff !important;
}

body.dark-mode input.form-control::placeholder{
    color:#999 !important;
}

/* الجدول */
body.dark-mode .table{
    background:#1b1b1b !important;
    color:#fff !important;
}

body.dark-mode .table thead{
    background:#252525 !important;
}

body.dark-mode .table thead th{
    background:#252525 !important;
    color:#ff944d !important;
    border-color:#333 !important;
    font-weight:700;
}

body.dark-mode .table tbody tr{
    background:#1b1b1b !important;
    transition:.2s;
}

body.dark-mode .table tbody tr:nth-child(even){
    background:#202020 !important;
}

body.dark-mode .table tbody tr:hover{
    background:#2b2b2b !important;
}

body.dark-mode .table td{
    color:#eee !important;
    border-color:#333 !important;
}

body.dark-mode .table th{
    border-color:#333 !important;
}

/* البيانات المهمة */
body.dark-mode .table strong{
    color:#fff !important;
}

/* بطاقة العملية */
body.dark-mode .badge-action{
    box-shadow:0 2px 8px rgba(0,0,0,.4);
}

/* زر البحث */
body.dark-mode .btn-warning{
    background:#ff6a00 !important;
    border-color:#ff6a00 !important;
    color:#fff !important;
}

body.dark-mode .btn-warning:hover{
    background:#ff7d26 !important;
    border-color:#ff7d26 !important;
}

/* العنوان */
body.dark-mode h3{
    color:#fff !important;
}

/* جدول responsive */
body.dark-mode .table-responsive{
    background:#1b1b1b !important;
}

/* Scrollbar */
body.dark-mode ::-webkit-scrollbar{
    width:8px;
    height:8px;
}

body.dark-mode ::-webkit-scrollbar-track{
    background:#1b1b1b;
}

body.dark-mode ::-webkit-scrollbar-thumb{
    background:#444;
    border-radius:10px;
}

body.dark-mode ::-webkit-scrollbar-thumb:hover{
    background:#666;
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