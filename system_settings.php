<?php
require __DIR__.'/partials/header.php'; 
//require_permission('settings.edit');

// جلب الإعدادات الحالية
$stmt = $pdo->query("SELECT * FROM system_settings WHERE id=1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')){

    $text1 = $_POST['text1'] ?? '';
    $text2 = $_POST['text2'] ?? '';
    $footer_text = $_POST['footer_text'] ?? '';

    // رفع لوجو رئيسي
    if(!empty($_FILES['main_logo']['name'])){
        $main_logo_name = 'main_logo_'.time().'_'.basename($_FILES['main_logo']['name']);
        move_uploaded_file($_FILES['main_logo']['tmp_name'], 'uploads/'.$main_logo_name);
    } else {
        $main_logo_name = $settings['main_logo'] ?? null;
    }

    // رفع لوجو فرعي
    if(!empty($_FILES['secondary_logo']['name'])){
        $secondary_logo_name = 'secondary_logo_'.time().'_'.basename($_FILES['secondary_logo']['name']);
        move_uploaded_file($_FILES['secondary_logo']['tmp_name'], 'uploads/'.$secondary_logo_name);
    } else {
        $secondary_logo_name = $settings['secondary_logo'] ?? null;
    }

    if($settings){
        // تحديث
        $stmt = $pdo->prepare("UPDATE system_settings SET main_logo=?, secondary_logo=?, text1=?, text2=?, footer_text=? WHERE id=1");
        $stmt->execute([$main_logo_name, $secondary_logo_name, $text1, $text2, $footer_text]);
    } else {
        // إدخال أول سجل
        $stmt = $pdo->prepare("INSERT INTO system_settings (main_logo, secondary_logo, text1, text2, footer_text) VALUES (?,?,?,?,?)");
        $stmt->execute([$main_logo_name, $secondary_logo_name, $text1, $text2, $footer_text]);
    }

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تم حفظ إعدادات النظام بنجاح'];
    header("Location: system_settings.php");
    exit;
}
?>

<div class="container py-4">

  <h3 class="mb-4"><i class="bi bi-gear-fill"></i> إعدادات النظام</h3>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label>اللوجو الرئيسي</label>
        <label class="custom-file-upload w-100">
          <i class="bi bi-image"></i>
          <span id="file-text-main"><?= esc($settings['main_logo'] ?? 'اختر صورة') ?></span>
          <input type="file" name="main_logo" accept="image/*" onchange="previewFile(this,'file-text-main','preview-main')">
          <img id="preview-main" style="max-width:150px;margin-top:10px;" src="<?= isset($settings['main_logo']) ? 'uploads/'.esc($settings['main_logo']) : '' ?>" />
        </label>
      </div>

      <div class="col-md-6">
        <label>اللوجو الفرعي</label>
        <label class="custom-file-upload w-100">
          <i class="bi bi-image"></i>
          <span id="file-text-secondary"><?= esc($settings['secondary_logo'] ?? 'اختر صورة') ?></span>
          <input type="file" name="secondary_logo" accept="image/*" onchange="previewFile(this,'file-text-secondary','preview-secondary')">
          <img id="preview-secondary" style="max-width:150px;margin-top:10px;" src="<?= isset($settings['secondary_logo']) ? 'uploads/'.esc($settings['secondary_logo']) : '' ?>" />
        </label>
      </div>

      <div class="col-md-6">
        <label>نص 1</label>
        <input type="text" name="text1" class="form-control" value="<?= esc($settings['text1'] ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label>نص 2</label>
        <input type="text" name="text2" class="form-control" value="<?= esc($settings['text2'] ?? '') ?>">
      </div>

      <div class="col-12">
        <label>نص الفوتر</label>
        <input type="text" name="footer_text" class="form-control" value="<?= esc($settings['footer_text'] ?? '') ?>">
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-orange">حفظ الإعدادات</button>
      </div>
    </div>

  </form>
</div>

<script>
function previewFile(input, textId, previewId){
    const file = input.files[0];
    if(file){
        document.getElementById(textId).textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require __DIR__.'/partials/footer.php'; ?>
