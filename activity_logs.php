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
        🌙 DARK MODE GLOBAL THEME
    ============================ */
    body.dark-mode {
      background-color: #121212 !important;
      color: #ffffff !important;
    }

    /* النص */
    body.dark-mode * {
      color: #eaeaea !important;
    }

    /* الروابط */
    body.dark-mode a {
      color: #ff944d !important;
    }

    /* النافبار */
    body.dark-mode .custom-navbar {
      background: rgba(18,18,18,0.9) !important;
      border-bottom: 1px solid #333 !important;
    }

    /* الأيقونات */
    body.dark-mode i {
      color: #ff944d !important;
    }

    /* الخلفيات العامة */
    body.dark-mode .card,
    body.dark-mode .table,
    body.dark-mode .modal-content,
    body.dark-mode .offcanvas,
    body.dark-mode .dropdown-menu,
    body.dark-mode .form-control,
    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea {
      background-color: #1e1e1e !important;
      color: #fff !important;
      border-color: #333 !important;
    }

    /* الكروت */
    body.dark-mode .card {
      box-shadow: 0 0 10px rgba(0,0,0,0.5) !important;
    }

    /* الجداول */
    body.dark-mode table {
      color: #fff !important;
    }
    body.dark-mode table tr {
      background: #1b1b1b !important;
    }
    body.dark-mode table td,
    body.dark-mode table th {
      border-color: #333 !important;
    }

    /* المودال */
    body.dark-mode .modal-header,
    body.dark-mode .modal-footer {
      border-color: #333 !important;
    }

    /* dropdown */
    body.dark-mode .dropdown-menu {
      background-color: #1f1f1f !important;
      border-color: #333 !important;
    }
    body.dark-mode .dropdown-item:hover {
      background-color: #333 !important;
    }

    /* البادجات */
    body.dark-mode .role-badge {
      background: #2c2c2c !important;
      color: #ff944d !important;
      border-color: #ff944d !important;
    }

    /* السايدبار */
    body.dark-mode .sidebar-link {
      color: #ddd !important;
    }
    body.dark-mode .sidebar-link:hover,
    body.dark-mode .sidebar-link.active {
      background: #333 !important;
      color: #ff944d !important;
    }

    /* loader */
    body.dark-mode .loader {
      background: #121212 !important;
    }
    body.dark-mode .loader-text {
      color: #ff944d !important;
    }

    /* buttons */
    body.dark-mode .btn-logout,
    body.dark-mode .btn-orange {
      background: #ff6a00 !important;
      color: white !important;
    }
    body.dark-mode .btn-orange:hover {
      background: #e65c00 !important;
    }

    /* حقول الإدخال */
    body.dark-mode .form-control {
      background: #1f1f1f !important;
      color: white !important;
      border-color: #444 !important;
    }

    body.dark-mode .form-control:focus {
      background: #222 !important;
      border-color: #ff944d !important;
      color: #fff !important;
    }
    /* خلفية الكونتينر */
    body.dark-mode .table-responsive {
        background-color: #1a1a1a !important;
        border-color: #333 !important;
    }

    /* خلفية الجدول */
    body.dark-mode .custom-table {
        background-color: #1a1a1a !important;
    }

    /* خلايا الجدول */
    body.dark-mode .custom-table td,
    body.dark-mode .custom-table th {
        background-color: #1e1e1e !important;
        color: #fff !important;
    }

    /* رأس الجدول */
    body.dark-mode .custom-table thead th {
        background-color: #222 !important;
        color: #fff !important;
    }

    /* الصفوف */
    body.dark-mode .custom-table tbody tr:nth-child(even) td {
        background-color: #262626 !important;
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