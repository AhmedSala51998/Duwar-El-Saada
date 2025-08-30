<?php require __DIR__.'/partials/header.php'; require_role('admin'); ?>
<?php $rows=$pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">المستخدمون</h3>
  <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-plus-lg"></i> مستخدم</button>
</div>
<table class="table table-hover"><thead class="table-light"><tr><th>#</th><th>اسم المستخدم</th><th>الدور</th><th>إنشاء</th><th>عمليات</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><?= $r['id'] ?></td><td><?= esc($r['username']) ?></td><td><?= esc($r['role']) ?></td><td><?= esc($r['created_at']) ?></td>
<td>
  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#e<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
  <?php if($r['id']!=$_SESSION['user_id']): ?><a onclick="return confirm('حذف المستخدم؟')" class="btn btn-sm btn-danger" href="user_delete.php?id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a><?php endif; ?>
</td></tr>
<div class="modal fade" id="e<?= $r['id'] ?>"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="user_edit.php"><input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <div class="modal-header"><h5 class="modal-title">تعديل مستخدم</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body vstack gap-3">
      <div><label class="form-label">اسم المستخدم</label><input name="username" class="form-control" value="<?= esc($r['username']) ?>" required></div>
      <div><label class="form-label">الدور</label><select name="role" class="form-select"><?php foreach(['admin','manager','staff'] as $ro): ?><option <?= $r['role']===$ro?'selected':'' ?>><?= $ro ?></option><?php endforeach; ?></select></div>
      <div><label class="form-label">كلمة مرور جديدة (اختياري)</label><input type="password" name="password" class="form-control"></div>
    </div><div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
  </form>
</div></div></div>
<?php endforeach; ?>
</tbody></table>

<div class="modal fade" id="add"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="user_add.php"><input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
    <div class="modal-header"><h5 class="modal-title">مستخدم جديد</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body vstack gap-3">
      <div><label class="form-label">اسم المستخدم</label><input name="username" class="form-control" required></div>
      <div><label class="form-label">كلمة المرور</label><input type="password" name="password" class="form-control" required></div>
      <div><label class="form-label">الدور</label><select name="role" class="form-select"><option>admin</option><option>manager</option><option>staff</option></select></div>
    </div><div class="modal-footer"><button class="btn btn-orange">حفظ</button></div>
  </form>
</div></div></div>
<?php require __DIR__.'/partials/footer.php'; ?>
