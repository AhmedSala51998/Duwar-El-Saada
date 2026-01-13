<?php
require __DIR__.'/partials/header.php';
require_permission('systems_settings.view');

$stmt = $pdo->query("SELECT * FROM system_settings ORDER BY id DESC LIMIT 1");
$setting = $stmt->fetch();
?>

<style>
:root{
  --main:#ff6a00;
  --bg:#f6f7fb;
  --card:#ffffff;
  --text:#222;
  --muted:#777;
  --border:#eee;
}

/* Layout */
body{
  background:var(--bg);
  color:var(--text);
}

.settings-wrapper{
  display:grid;
  grid-template-columns:280px 1fr;
  gap:30px;
}

/* Tabs */
.settings-tabs{
  display:flex;
  flex-direction:column;
  gap:18px;
}

.tab-card{
  background:var(--card);
  border-radius:22px;
  padding:22px 20px;
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:15px;
  box-shadow:0 15px 40px rgba(0,0,0,.08);
  border:2px solid transparent;
  transition:.35s;
}

.tab-card i{
  font-size:26px;
  color:var(--main);
}

.tab-card h6{
  margin:0;
  font-weight:600;
  color:var(--text);
}

.tab-card p{
  margin:0;
  font-size:13px;
  color:var(--muted);
}

.tab-card:hover{
  transform:translateY(-3px);
}

.tab-card.active{
  border-color:var(--main);
  background:linear-gradient(135deg,#fff7f0,#ffffff);
}

/* Content */
.settings-content{
  background:var(--card);
  border-radius:26px;
  padding:35px;
  box-shadow:0 20px 50px rgba(0,0,0,.1);
}

.tab-content{display:none;}
.tab-content.active{display:block;}

/* Upload */
.custom-file-upload{
  border:2px dashed var(--border);
  border-radius:18px;
  padding:28px;
  text-align:center;
  transition:.3s;
  cursor:pointer;
}

.custom-file-upload:hover{
  border-color:var(--main);
  background:rgba(255,106,0,.05);
}

.custom-file-upload i{
  font-size:42px;
  color:var(--main);
}

.custom-file-upload img{
  max-height:130px;
  margin-top:15px;
  border-radius:14px;
}

.custom-file-upload input{display:none;}

/* Buttons */
.btn-orange{
  background:var(--main);
  color:#fff;
  border:0;
  padding:10px 26px;
  border-radius:14px;
}

.btn-orange:hover{
  opacity:.9;
}

/* Mobile */
@media(max-width:991px){
  .settings-wrapper{
    grid-template-columns:1fr;
  }

  .settings-tabs{
    flex-direction:row;
    gap:15px;
    overflow-x:auto;
    padding-bottom:10px;
  }

  .tab-card{
    min-width:240px;
    flex-shrink:0;
  }
}
</style>

<h3 class="mb-4 d-flex align-items-center gap-2">
  <i class="bi bi-gear-fill text-warning"></i> إعدادات النظام
</h3>

<div class="settings-wrapper">

  <!-- Tabs -->
  <div class="settings-tabs">
    <div class="tab-card active" data-tab="logos">
      <i class="bi bi-image"></i>
      <div>
        <h6>الشعارات</h6>
        <p>إدارة صور النظام</p>
      </div>
    </div>

    <div class="tab-card" data-tab="texts">
      <i class="bi bi-fonts"></i>
      <div>
        <h6>النصوص</h6>
        <p>العناوين والمحتوى</p>
      </div>
    </div>

    <div class="tab-card" data-tab="footer">
      <i class="bi bi-layout-text-window"></i>
      <div>
        <h6>الفوتر</h6>
        <p>نص أسفل الموقع</p>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="settings-content">

    <!-- Logos -->
    <div class="tab-content active" id="logos">
      <h4 class="mb-4">الشعارات</h4>
      <form method="post" action="setting_edit" enctype="multipart/form-data" class="vstack gap-4">
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

        <button class="btn-orange align-self-start">حفظ التغييرات</button>
      </form>
    </div>

    <!-- Texts -->
    <div class="tab-content" id="texts">
      <h4 class="mb-4">النصوص</h4>
      <form method="post" action="setting_edit" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <input class="form-control" name="text1" value="<?= esc($setting['text1']) ?>" placeholder="النص الأول">
        <textarea class="form-control" name="text2" rows="4"><?= esc($setting['text2']) ?></textarea>

        <button class="btn-orange align-self-start">حفظ</button>
      </form>
    </div>

    <!-- Footer -->
    <div class="tab-content" id="footer">
      <h4 class="mb-4">الفوتر</h4>
      <form method="post" action="setting_edit" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $setting['id'] ?>">

        <textarea class="form-control" name="footer_text" rows="5"><?= esc($setting['footer_text']) ?></textarea>
        <button class="btn-orange align-self-start">حفظ</button>
      </form>
    </div>

  </div>
</div>

<script>
document.querySelectorAll('.tab-card').forEach(tab=>{
  tab.onclick=()=>{
    document.querySelectorAll('.tab-card').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById(tab.dataset.tab).classList.add('active');
  }
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
