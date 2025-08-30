<?php
define('APP_NAME','دوار السعادة');
define('BASE_URL','/duwar_al_saada_dashboard_v5');
date_default_timezone_set('Asia/Riyadh');
session_start();

// DB
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'duwar_al_saada';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

try{
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
}catch(Exception $e){ die('DB connection failed: '.$e->getMessage()); }

// ---- Helpers ----
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_auth(){ return !empty($_SESSION['user_id']); }
function current_user(){ return $_SESSION['username'] ?? 'guest'; }
function current_role(){ return $_SESSION['role'] ?? 'staff'; }
function require_auth(){ if(!is_auth()){ header('Location: '.BASE_URL.'/login.php'); exit; } }
function require_role($roles){
  $roles = is_array($roles)?$roles:[$roles];
  if(!is_auth()){ header('Location: '.BASE_URL.'/login.php'); exit; }
  if(!in_array(current_role(), $roles)){
    http_response_code(403);
    echo "<div style='direction:rtl;padding:2rem;font-family:Cairo,sans-serif'>لا تملك صلاحية الوصول.</div>";
    exit;
  }
}
// CSRF
function csrf_token(){ if(empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function csrf_validate($t){ return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'],$t); }
// Upload
function upload_image($field,$dir='uploads'){
  if(empty($_FILES[$field]['name'])) return null;
  if(!is_dir($dir)) mkdir($dir,0777,true);
  $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
  if(!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return null;
  $name = uniqid('img_').'.'.$ext;
  move_uploaded_file($_FILES[$field]['tmp_name'], $dir.'/'.$name);
  return $name;
}
// Flash
function flash($key,$msg=null){
  if($msg===null){
    $m = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $m;
  } else {
    $_SESSION['flash'][$key] = $msg;
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
