<?php
require __DIR__.'/partials/header.php';
require_permission('systems_settings.view');

/* Toast */
if(!empty($_SESSION['toast'])):
$toast=$_SESSION['toast']; unset($_SESSION['toast']);
?>
<div class="position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div class="toast align-items-center text-bg-<?= $toast['type'] ?> border-0 show">
    <div class="d-flex">
      <div class="toast-body"><?= esc($toast['msg']) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
/* إعداد واحد فقط */
$stmt = $pdo->query("SELECT * FROM system_settings ORDER BY id DESC LIMIT 1");
$setting = $stmt->fetch();
?>

<style>
.settings-wrapper{
  display:flex;
  gap:25px;
  align-items:flex-start;
}

/* Tabs */
.settings-tabs{
  width:230px;
  background:#fff;
  border-radius:18px;
  padding:10px;
  box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.tab-btn{
  width:100%;
  border:0;
  background:transparent;
  padding:14px 16px;
  border-radius:14px;
  text-align:right;
  display:flex;
  align-items:center;
  gap:10px;
  font-weight:500;
  color:#555;
  transition:.3s;
}
.tab-btn i{font-size:18px;}
.tab-btn:hover,
.tab-btn.active{
  background:#fff1e6;
  color:#ff6a00;
}

/* Content */
.settings-content{
  flex:1;
  background:#fff;
  border-radius:22px;
  padding:25px;
  box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.tab-content{display:none;}
.tab-content.active{display:block;}

/* Upload */
.custom-file-upload{
  border:2px dashed #ddd;
  border-radius:14px;
  padding:20px;
  text-align:center;
  cursor:pointer;
  transition:.3s;
}
.custom-file-upload:hover{
  border-color:#ff6a00;
  background:#fff7f0;
}
.custom-file-upload i{
  font-size:36px;
  color:#ff6a00;
}
.custom-file-upload img{
  max-height:110px;
  margin-top:10px;
  border-radius:10px;
}
.custom-file-upload input{display:none;}

.btn-orange{
  background:#ff6a00;
  color:#fff;
  border:0;
}
.btn-orange:hover{background:#e85d00}
</style>

<h3 class="mb-4 d-flex align-items-center gap-2">
  <i class="bi bi-gear-fill text-warning"></i> إعدادات النظام
</h3>

<div class="settings-wrapper">

  <!-- Tabs -->
  <div class="settings-tabs">
    <button class="tab-btn active" data-tab="logos">
      <i class="bi bi-image"></i> الشعارات
    </button>
    <button class="tab-btn" data-tab="texts">
      <i class="bi bi-fonts"></i> النصوص
    </button>
    <button class="tab-btn" data-tab="footer">
      <i class="bi bi-layout-text-window"></i> الفوتر
    </button>
  </div>

  <!-- Content -->
  <div class="settings-content">

    <!-- LOGOS -->
    <div class="tab-content active" id="logos">
      <h5 class="mb-3">الشعارات</h5>
      <form method="post" action="setting_edit" enctype="multipart/form-data" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <label class="custom-file-upload">
          <i class="bi bi-image"></i>
          <p class="mb-1">الشعار الرئيسي</p>
          <input type="file" name="main_logo" accept="image/*">
          <?php if($setting['main_logo']): ?>
            <img src="<?= esc($setting['main_logo']) ?>">
          <?php endif; ?>
        </label>

        <label class="custom-file-upload">
          <i class="bi bi-image"></i>
          <p class="mb-1">الشعار الثانوي</p>
          <input type="file" name="secondary_logo" accept="image/*">
          <?php if($setting['secondary_logo']): ?>
            <img src="<?= esc($setting['secondary_logo']) ?>">
          <?php endif; ?>
        </label>

        <button class="btn btn-orange align-self-start">حفظ</button>
      </form>
    </div>

    <!-- TEXTS -->
    <div class="tab-content" id="texts">
      <h5 class="mb-3">النصوص</h5>
      <form method="post" action="setting_edit" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <input class="form-control" name="text1" value="<?= esc($setting['text1']) ?>" placeholder="النص الأول">
        <textarea class="form-control" name="text2" rows="3" placeholder="النص الثاني"><?= esc($setting['text2']) ?></textarea>

        <button class="btn btn-orange align-self-start">حفظ</button>
      </form>
    </div>

    <!-- FOOTER -->
    <div class="tab-content" id="footer">
      <h5 class="mb-3">نص الفوتر</h5>
      <form method="post" action="setting_edit" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <textarea class="form-control" name="footer_text" rows="4"><?= esc($setting['footer_text']) ?></textarea>

        <button class="btn btn-orange align-self-start">حفظ</button>
      </form>
    </div>

  </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.onclick=()=>{
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.tab).classList.add('active');
  }
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
