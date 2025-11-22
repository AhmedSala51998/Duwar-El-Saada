<?php
if(!defined('APP_NAME')) define('APP_NAME','دوار السعادة');
if(!defined('BASE_URL')) define('BASE_URL','');
date_default_timezone_set('Asia/Riyadh');
if(session_status() === PHP_SESSION_NONE) session_start();

// DB
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'u552468652_duwar_al_saada';
$DB_USER = getenv('DB_USER') ?: 'u552468652_duwar_al_saada';
$DB_PASS = getenv('DB_PASS') ?: 'Duwar_al_saada12345@';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(Exception $e) {
    die('DB connection failed: '.$e->getMessage());
}

// ---- Helpers ----
if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}


if (!function_exists('getSystemSettings')) {
    /**
     * ترجع إعدادات النظام من جدول system_settings
     * @param string|null $key اسم العمود المطلوب
     * @return mixed القيمة أو مصفوفة كاملة
     */
    function getSystemSettings($key = null) {
        global $pdo; // الاتصال PDO الموجود عندك

        static $settings = null;

        // جلب البيانات مرة واحدة فقط لكل طلب
        if ($settings === null) {
            try {
                $stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
                $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $settings = [];
            }
        }

        if ($key !== null) {
            return isset($settings[$key]) ? $settings[$key] : null;
        }

        return $settings;
    }
}

if (!function_exists('is_auth')) {
    function is_auth(){ return !empty($_SESSION['user_id']); }
}

if (!function_exists('current_user')) {
    function current_user(){ return $_SESSION['username'] ?? 'guest'; }
}

if (!function_exists('current_user_id_seq')) {
    function current_user_id_seq(){ return $_SESSION['user_id_seq'] ?? 'Ad0001'; }
}

if (!function_exists('current_role')) {
    function current_role() {
        global $pdo;
        if (empty($_SESSION['user_id'])) return 'guest';

        $user_id = $_SESSION['user_id'];

        // استعلام يجلب اسم الدور من جدول roles
        $stmt = $pdo->prepare("
            SELECT r.name 
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $role_name = $stmt->fetchColumn();

        return $role_name ?: 'staff';
    }
}

if (!function_exists('require_auth')) {
    function require_auth(){ if(!is_auth()){ header('Location: '.BASE_URL.'/login.php'); exit; } }
}

if (!function_exists('require_role')) {
    function require_role($roles){
        $roles = is_array($roles)?$roles:[$roles];
        if(!is_auth()){ header('Location: '.BASE_URL.'/login.php'); exit; }
        if(!in_array(current_role(), $roles)){
            http_response_code(403);
            echo "<div style='direction:rtl;padding:2rem;font-family:Cairo,sans-serif'>لا تملك صلاحية الوصول.</div>";
            exit;
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(){ if(empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate($t){ return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'],$t); }
}

if (!function_exists('upload_image')) {
    function upload_image($field,$dir='uploads'){
        if(empty($_FILES[$field]['name'])) return null;
        if(!is_dir($dir)) mkdir($dir,0777,true);
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return null;
        $name = uniqid('img_').'.'.$ext;
        move_uploaded_file($_FILES[$field]['tmp_name'], $dir.'/'.$name);
        return $name;
    }
}

if (!function_exists('flash')) {
    function flash($key,$msg=null){
        if($msg===null){
            $m = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $m;
        } else {
            $_SESSION['flash'][$key] = $msg;
        }
    }
}

// 🔹 دالة تتحقق هل المستخدم عنده صلاحية معينة
function has_permission($perm_code) {
    global $pdo;

    if (!isset($_SESSION['user_id'])) return false;

    // جلب الدور
    $user_id = $_SESSION['user_id'];
    $role_id = $pdo->query("SELECT role_id FROM users WHERE id=$user_id")->fetchColumn();
    if (!$role_id) return false;

    // تحقق من وجود العلاقة بين الدور والصلاحية
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM role_permissions rp
        JOIN permissions p ON p.id = rp.permission_id
        WHERE rp.role_id = ? AND p.code = ?
    ");
    $stmt->execute([$role_id, $perm_code]);
    return $stmt->fetchColumn() > 0;
}

// 🔹 دالة تمنع الوصول في حالة عدم وجود صلاحية
function require_permission($perm_code) {
    if (!has_permission($perm_code)) {
        $_SESSION['toast'] = ['type'=>'danger','msg'=>'ليس لديك صلاحية للوصول إلى هذه الصفحة.'];
        header('Location: ' . BASE_URL . '/home.php');
        exit;
    }
}

// ---- Schema (auto-bootstrap) ----
$pdo->exec("CREATE TABLE IF NOT EXISTS users(
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS purchases(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  unit ENUM('عدد','جرام','كيلو','لتر') NOT NULL DEFAULT 'عدد',
  quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
  price DECIMAL(12,2) DEFAULT 0,
  product_image VARCHAR(255) NULL,
  invoice_image VARCHAR(255) NULL,
  payer_name VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS orders(
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
  qty DECIMAL(12,3) NOT NULL,
  unit ENUM('عدد','جرام','كيلو','لتر') NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_p FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS assets(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(12,2) DEFAULT 0,
  payer_name VARCHAR(255) NULL,
  image VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// default admin if empty
$c = (int)$pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
if($c===0){
  $pdo->prepare("INSERT INTO users(username,password_hash,role) VALUES(?,?, 'admin')")
      ->execute(['admin', password_hash('123456', PASSWORD_DEFAULT)]);
}
