<?php
require __DIR__.'/partials/header.php';
require_permission('systems_settings.view');

$stmt = $pdo->query("SELECT * FROM system_settings ORDER BY id DESC LIMIT 1");
$setting = $stmt->fetch();
?>

<style>
:root {
  --main: #ff6a00;
  --accent: #ff9a33;
  --bg: #f6f7fb;
  --card: #ffffff;
  --text: #111;
  --muted: #666;
  --border: #e5e5e5;
  --radius: 5px;
  --shadow: 0 10px 30px rgba(0,0,0,.08);
}

/* BODY */
body {
  background: var(--bg);
  color: var(--text);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* LAYOUT */
.settings-wrapper {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 40px;
  padding: 20px;
}

/* TABS */
.settings-tabs {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.tab-card {
  background: var(--card);
  padding: 28px 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 18px;
  border: 2px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  transition: all 0.3s ease;
}

.tab-card i {
  font-size: 32px;
  color: var(--main);
}

.tab-card h6 {
  margin: 0;
  font-weight: 700;
  font-size: 16px;
}

.tab-card p {
  margin: 2px 0 0;
  font-size: 13px;
  color: var(--muted);
}

.tab-card:hover {
  border-color: var(--main);
  background: linear-gradient(135deg, #fff7f0, #ffffff);
  transform: translateY(-3px);
}

.tab-card.active {
  border-color: var(--main);
  background: linear-gradient(135deg, #fff1e6, #fff7f0);
  box-shadow: 0 15px 35px rgba(255,106,0,.2);
}

/* CONTENT */
.settings-content {
  background: var(--card);
  padding: 40px;
  border-radius: var(--radius);
  border: 2px solid var(--border);
  box-shadow: var(--shadow);
  transition: all 0.3s ease;
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

/* FILE UPLOAD */
.custom-file-upload {
  border: 2px dashed var(--border);
  padding: 35px;
  text-align: center;
  cursor: pointer;
  border-radius: var(--radius);
  position: relative;
  transition: all 0.3s ease;
}

.custom-file-upload:hover {
  border-color: var(--main);
  background: rgba(255,106,0,.05);
  transform: scale(1.02);
}

.custom-file-upload i {
  font-size: 48px;
  color: var(--main);
}

.custom-file-upload img {
  max-height: 140px;
  margin-top: 18px;
  border-radius: var(--radius);
  box-shadow: 0 5px 15px rgba(0,0,0,.08);
}

.custom-file-upload input { display: none; }

/* BUTTONS */
.btn-orange {
  background: var(--main);
  color: #fff;
  border: 0;
  padding: 14px 36px;
  font-weight: 700;
  border-radius: var(--radius);
  box-shadow: 0 6px 18px rgba(255,106,0,.3);
  transition: all 0.3s ease;
}

.btn-orange:hover {
  opacity: .95;
  transform: translateY(-2px);
  box-shadow: 0 8px 22px rgba(255,106,0,.4);
}

/* PAGE TITLE */
.page-title {
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
}

.page-title i {
  color: var(--main);
  font-size: 28px;
}

/* MOBILE */
@media(max-width:991px) {
  .settings-wrapper {
    grid-template-columns: 1fr;
    gap: 25px;
  }

  .settings-tabs {
    flex-direction: row;
    overflow-x: auto;
    gap: 15px;
  }

  .tab-card {
    min-width: 220px;
    flex-shrink: 0;
    padding: 20px;
  }

  .tab-card i { font-size: 36px; }
  .tab-card h6 { font-size: 15px; }

  .settings-content {
    padding: 25px;
  }

  .custom-file-upload { padding: 25px; }
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
