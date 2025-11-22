<?php
require __DIR__.'/config/config.php';
//require_permission('settings.edit');

if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $id = $_POST['id'] ?? null;
    $text1 = trim($_POST['text1'] ?? '');
    $text2 = trim($_POST['text2'] ?? '');
    $footer_text = trim($_POST['footer_text'] ?? '');

    // رفع الصور
    function upload_image($file, $old=null){
        if(isset($file) && $file['tmp_name']){
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $name = uniqid().'_logo.'.$ext;
            move_uploaded_file($file['tmp_name'], __DIR__.'/uploads/'.$name);
            return $name;
        }
        return $old;
    }

    // جلب الإعدادات القديمة
    $old = $id ? $pdo->query("SELECT * FROM system_settings WHERE id=$id")->fetch() : [];

    $main_logo = upload_image($_FILES['main_logo'], $old['main_logo'] ?? null);
    $sub_logo = upload_image($_FILES['sub_logo'], $old['sub_logo'] ?? null);

    if($id){
        $stmt = $pdo->prepare("UPDATE system_settings SET main_logo=?, sub_logo=?, text1=?, text2=?, footer_text=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$main_logo,$sub_logo,$text1,$text2,$footer_text,$id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO system_settings (main_logo, sub_logo, text1, text2, footer_text) VALUES (?,?,?,?,?)");
        $stmt->execute([$main_logo,$sub_logo,$text1,$text2,$footer_text]);
    }

    $_SESSION['toast'] = ['type'=>'success','msg'=>'تم حفظ إعدادات النظام بنجاح'];
    header('Location: system_settings.php');
    exit;
}
?>
