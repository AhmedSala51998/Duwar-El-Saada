<?php
require __DIR__.'/partials/header.php';
require_permission('systems_settings.view');

$stmt = $pdo->query("SELECT * FROM system_settings ORDER BY id DESC LIMIT 1");
$setting = $stmt->fetch();
?>

<style>
:root{
  --bg:#ffffff;
  --card:#ffffff;
  --text:#212529;
  --muted:#6c757d;
  --border:#e5e5e5;
  --orange:#ff6a00;
  --soft-orange:#fff1e6;
  --shadow:0 10px 25px rgba(0,0,0,.08);
}

/* 🌙 Dark mode */
@media (prefers-color-scheme: dark){
  :root{
    --bg:#0f1115;
    --card:#1a1d23;
    --text:#f1f1f1;
    --muted:#9aa0a6;
    --border:#2a2e35;
    --soft-orange:#2a1a10;
    --shadow:0 10px 25px rgba(0,0,0,.4);
  }
}

body{background:var(--bg); color:var(--text);}

.settings-wrapper{
  display:flex;
  gap:24px;
  align-items:flex-start;
}

/* Tabs */
.settings-tabs{
  width:230px;
  background:var(--card);
  border-radius:18px;
  padding:10px;
  box-shadow:var(--shadow);
  border:1px solid var(--border);
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
  color:var(--text);
  transition:.3s;
}
.tab-btn i{font-size:18px}
.tab-btn:hover,
.tab-btn.active{
  background:var(--soft-orange);
  color:var(--orange);
}

/* Content */
.settings-content{
  flex:1;
  background:var(--card);
  border-radius:22px;
  padding:24px;
  box-shadow:var(--shadow);
  border:1px solid var(--border);
}

.tab-content{display:none;}
.tab-content.active{display:block;}

.custom-file-upload{
  border:2px dashed var(--border);
  border-radius:14px;
  padding:18px;
  text-align:center;
  cursor:pointer;
  transition:.3s;
  background:transparent;
}
.custom-file-upload:hover{
  border-color:var(--orange);
  background:var(--soft-orange);
}
.custom-file-upload i{
  font-size:36px;
  color:var(--orange);
}
.custom-file-upload img{
  max-height:110px;
  margin-top:10px;
  border-radius:10px;
}
.custom-file-upload input{display:none}

.btn-orange{
  background:var(--orange);
  color:#fff;
  border:0;
}
.btn-orange:hover{background:#e85d00}

/* 📱 Mobile Responsive */
@media(max-width: 768px){
  .settings-wrapper{
    flex-direction:column;
  }

  .settings-tabs{
    width:100%;
    display:flex;
    gap:10px;
    overflow-x:auto;
  }

  .tab-btn{
    white-space:nowrap;
    justify-content:center;
    text-align:center;
    min-width:140px;
    flex-shrink:0;
  }

  .settings-content{
    padding:18px;
  }
}
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

    <div class="tab-content active" id="logos">
      <h5 class="mb-3">الشعارات</h5>
      <form method="post" action="setting_edit" enctype="multipart/form-data" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <label class="custom-file-upload">
          <i class="bi bi-image"></i>
          <p>الشعار الرئيسي</p>
          <input type="file" name="main_logo">
          <?php if($setting['main_logo']): ?>
            <img src="<?= esc($setting['main_logo']) ?>">
          <?php endif; ?>
        </label>

        <label class="custom-file-upload">
          <i class="bi bi-image"></i>
          <p>الشعار الثانوي</p>
          <input type="file" name="secondary_logo">
          <?php if($setting['secondary_logo']): ?>
            <img src="<?= esc($setting['secondary_logo']) ?>">
          <?php endif; ?>
        </label>

        <button class="btn btn-orange align-self-start">حفظ</button>
      </form>
    </div>

    <div class="tab-content" id="texts">
      <h5 class="mb-3">النصوص</h5>
      <form method="post" action="setting_edit" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <input class="form-control" name="text1" value="<?= esc($setting['text1']) ?>" placeholder="النص الأول">
        <textarea class="form-control" name="text2" rows="3"><?= esc($setting['text2']) ?></textarea>

        <button class="btn btn-orange align-self-start">حفظ</button>
      </form>
    </div>

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
