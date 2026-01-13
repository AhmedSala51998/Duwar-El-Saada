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
  --text:#111;
  --muted:#666;
  --border:#e5e5e5;
  --radius:10px;
}

/* Layout */

.settings-wrapper{
  display:grid;
  grid-template-columns:300px 1fr;
  gap:40px;
}

/* Tabs */
.settings-tabs{
  display:flex;
  flex-direction:column;
  gap:20px;
}

.tab-card{
  background:var(--card);
  padding:26px 24px;
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:18px;
  border:2px solid var(--border);
  transition:.25s;
}

.tab-card i{
  font-size:30px;
  color:var(--main);
}

.tab-card h6{
  margin:0;
  font-weight:700;
  font-size:16px;
}

.tab-card p{
  margin:2px 0 0;
  font-size:13px;
  color:var(--muted);
}

.tab-card:hover{
  border-color:var(--main);
  background:#fff7f0;
}

.tab-card.active{
  border-color:var(--main);
  background:#fff1e6;
}

/* Content */
.settings-content{
  background:var(--card);
  padding:40px;
  border:2px solid var(--border);
}

.tab-content{display:none;}
.tab-content.active{display:block;}

/* Upload */
.custom-file-upload{
  border:2px dashed var(--border);
  padding:35px;
  text-align:center;
  transition:.3s;
  cursor:pointer;
}

.custom-file-upload:hover{
  border-color:var(--main);
  background:#fff7f0;
}

.custom-file-upload i{
  font-size:46px;
  color:var(--main);
}

.custom-file-upload img{
  max-height:140px;
  margin-top:18px;
}

.custom-file-upload input{display:none;}

/* Buttons */
.btn-orange{
  background:var(--main);
  color:#fff;
  border:0;
  padding:12px 32px;
  font-weight:600;
}

.btn-orange:hover{
  opacity:.9;
}

/* ========== MOBILE UX PRO ========== */
@media(max-width:991px){

  .settings-wrapper{
    grid-template-columns:1fr;
    gap:25px;
  }

  /* Tabs become BIG blocks */
  .settings-tabs{
    gap:14px;
  }

  .tab-card{
    width:100%;
    padding:22px;
    gap:15px;
  }

  .tab-card i{
    font-size:34px;
  }

  .tab-card h6{
    font-size:15px;
  }

  .settings-content{
    padding:25px;
  }

  .custom-file-upload{
    padding:25px;
  }
}
.tab-card{
  border-radius:var(--radius);
}
.settings-content{
  border-radius:var(--radius);
}
.custom-file-upload{
  border-radius:var(--radius);
}
.custom-file-upload img{
  border-radius:var(--radius);
}
.btn-orange{
  border-radius:var(--radius);
}
.page-title {
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
}
</style>

<h3 class="mb-4 page-title d-flex align-items-center gap-2">
  <span class="stat-icon me-2">
    <i class="bi bi-gear-fill"></i>
  </span>
  إعدادات النظام
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
