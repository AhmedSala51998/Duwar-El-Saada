<?php
require __DIR__.'/partials/header.php';
require_permission('dhimam.view');

$kw = trim($_GET['kw'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$like = '%'.$kw.'%';

$count = $pdo->prepare(
    'SELECT COUNT(*) FROM dhimam
     WHERE person_name LIKE ? OR description LIKE ?'
);
$count->execute([$like, $like]);

$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT id, person_name, amount, description, received_at
     FROM dhimam
     WHERE person_name LIKE ? OR description LIKE ?
     ORDER BY received_at DESC, id DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute([$like, $like]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$canEdit = has_permission('dhimam.edit');
$canDelete = has_permission('dhimam.delete');
?>

<style>
.dhimam-title {
    font-weight: 700;
    color: #2c3e50;
}
.dhimam-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #fff1e6;
    color: #ff6a00;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-orange {
    background: #ff6a00;
    color: #fff;
    border: 0;
}
.btn-orange:hover {
    background: #e85d00;
    color: #fff;
}
.dhimam-table th {
    background: #f8f9fa;
    white-space: nowrap;
}
.dhimam-table td,
.dhimam-table th {
    padding: .7rem;
    vertical-align: middle;
}
.pagination .page-link {
    color: #ff6a00;
}
.pagination .active .page-link {
    background: #ff6a00;
    border-color: #ff6a00;
    color: #fff;
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
/* جسم الجدول */
body.dark-mode .table tbody,
body.dark-mode .table tbody tr,
body.dark-mode .table tbody td{
    background:#1b1b1b !important;
    color:#fff !important;
}

/* صفوف متبادلة */
body.dark-mode .table tbody tr:nth-child(even){
    background:#222 !important;
}

body.dark-mode .table tbody tr:nth-child(even) td{
    background:#222 !important;
}

/* Hover */
body.dark-mode .table-hover tbody tr:hover,
body.dark-mode .table-hover tbody tr:hover td{
    background:#2d2d2d !important;
}

/* الهيدر */
body.dark-mode .table-light,
body.dark-mode .table-light th{
    background:#252525 !important;
    color:#ff944d !important;
}

/* Responsive wrapper */
body.dark-mode .table-responsive{
    background:#1b1b1b !important;
}

/* الحدود */
body.dark-mode .table>:not(caption)>*>*{
    border-color:#333 !important;
}

/* لو Bootstrap عامل table-striped */
body.dark-mode .table-striped>tbody>tr:nth-of-type(odd)>*{
    background:#1f1f1f !important;
    color:#fff !important;
}



body.dark-mode .table tbody tr{
    background:#171717 !important;
}

body.dark-mode .table tbody tr:nth-child(even){
    background:#1d1d1d !important;
}

body.dark-mode .table-hover tbody tr:hover td{
    background:#2a2a2a !important;
    box-shadow: inset 4px 0 0 #ff6a00;
}
</style>

<?php if (!empty($_SESSION['toast'])):
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
?>
<div class="alert alert-<?= esc($toast['type']) ?> alert-dismissible fade show"
     role="alert">
    <?= esc($toast['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"
            aria-label="إغلاق"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h3 class="dhimam-title mb-0 d-flex align-items-center gap-2">
        <span class="dhimam-icon">
            <i class="bi bi-cash-coin"></i>
        </span>
        الذمم
    </h3>

    <div class="d-flex align-items-center flex-wrap gap-2">
        <form method="get" class="d-flex gap-2">
            <input name="kw"
                   class="form-control"
                   placeholder="بحث بالاسم أو البيان"
                   value="<?= esc($kw) ?>">

            <button class="btn btn-orange" aria-label="بحث">
                <i class="bi bi-search"></i>
            </button>

            <?php if ($kw !== ''): ?>
                <a href="dhimam.php" class="btn btn-outline-secondary">مسح</a>
            <?php endif; ?>
        </form>

        <?php if (has_permission('dhimam.add')): ?>
            <button class="btn btn-orange"
                    data-bs-toggle="modal"
                    data-bs-target="#addDhimma">
                <i class="bi bi-plus-lg"></i> إضافة ذمة
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive shadow-sm rounded-3 border bg-white p-2">
    <table class="table table-hover dhimam-table mb-0 text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الشخص</th>
                <th>المبلغ المستلم</th>
                <th>البيان / الوصف</th>
                <th>تاريخ الاستلام</th>
                <?php if ($canEdit || $canDelete): ?>
                    <th>عمليات</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><?= esc($row['person_name']) ?></td>
                    <td><?= number_format((float)$row['amount'], 2) ?></td>
                    <td class="text-wrap" style="min-width:180px;max-width:400px">
                        <?= nl2br(esc($row['description'])) ?>
                    </td>
                    <td><?= esc($row['received_at']) ?></td>

                    <?php if ($canEdit || $canDelete): ?>
                        <td class="text-nowrap">
                            <?php if ($canEdit): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#edit<?= (int)$row['id'] ?>"
                                        aria-label="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            <?php endif; ?>

                            <?php if ($canDelete): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#delete<?= (int)$row['id'] ?>"
                                        aria-label="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 6 : 5 ?>"
                        class="text-muted py-4">
                        لا توجد ذمم مسجلة
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
<nav class="mt-3" aria-label="صفحات الذمم">
    <ul class="pagination justify-content-center flex-wrap">
        <?php
        for (
            $i = max(1, $page - 2);
            $i <= min($pages, $page + 2);
            $i++
        ):
        ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link"
                   href="?<?= esc(http_build_query([
                       'kw' => $kw,
                       'page' => $i
                   ])) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php if ($canEdit || $canDelete): ?>
    <?php foreach ($rows as $row):
        $id = (int)$row['id'];
    ?>

        <?php if ($canEdit): ?>
        <div class="modal fade" id="edit<?= $id ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="dhimam_edit">
                        <input type="hidden" name="_csrf"
                               value="<?= esc(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">

                        <div class="modal-header">
                            <h5 class="modal-title">تعديل ذمة</h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                        </div>

                        <div class="modal-body vstack gap-3">
                            <div>
                                <label class="form-label">اسم الشخص</label>
                                <input name="person_name"
                                       class="form-control"
                                       maxlength="255"
                                       value="<?= esc($row['person_name']) ?>"
                                       required>
                            </div>

                            <div>
                                <label class="form-label">المبلغ المستلم</label>
                                <input name="amount"
                                       type="number"
                                       min="0.01"
                                       max="9999999999999.99"
                                       step="0.01"
                                       class="form-control"
                                       value="<?= esc($row['amount']) ?>"
                                       required>
                            </div>

                            <div>
                                <label class="form-label">البيان / الوصف</label>
                                <textarea name="description"
                                          class="form-control"
                                          rows="3"
                                          required><?= esc($row['description']) ?></textarea>
                            </div>

                            <div>
                                <label class="form-label">تاريخ الاستلام</label>
                                <input name="received_at"
                                       type="date"
                                       class="form-control"
                                       value="<?= esc($row['received_at']) ?>"
                                       required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">إلغاء</button>
                            <button class="btn btn-orange">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canDelete): ?>
        <div class="modal fade" id="delete<?= $id ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="dhimam_delete">
                        <input type="hidden" name="_csrf"
                               value="<?= esc(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">

                        <div class="modal-header">
                            <h5 class="modal-title">تأكيد الحذف</h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                        </div>

                        <div class="modal-body">
                            هل تريد حذف ذمة
                            <strong><?= esc($row['person_name']) ?></strong>
                            بمبلغ <?= number_format((float)$row['amount'], 2) ?>؟
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">إلغاء</button>
                            <button class="btn btn-danger">حذف</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endforeach; ?>
<?php endif; ?>

<?php if (has_permission('dhimam.add')): ?>
<div class="modal fade" id="addDhimma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="dhimam_add">
                <input type="hidden" name="_csrf"
                       value="<?= esc(csrf_token()) ?>">

                <div class="modal-header">
                    <h5 class="modal-title">إضافة ذمة</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="إغلاق"></button>
                </div>

                <div class="modal-body vstack gap-3">
                    <div>
                        <label class="form-label">اسم الشخص</label>
                        <input name="person_name"
                               class="form-control"
                               maxlength="255"
                               required>
                    </div>

                    <div>
                        <label class="form-label">المبلغ المستلم</label>
                        <input name="amount"
                               type="number"
                               min="0.01"
                               max="9999999999999.99"
                               step="0.01"
                               class="form-control"
                               required>
                    </div>

                    <div>
                        <label class="form-label">البيان / الوصف</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  required></textarea>
                    </div>

                    <div>
                        <label class="form-label">تاريخ الاستلام</label>
                        <input name="received_at"
                               type="date"
                               class="form-control"
                               value="<?= date('Y-m-d') ?>"
                               required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">إلغاء</button>
                    <button class="btn btn-orange">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/partials/footer.php'; ?>