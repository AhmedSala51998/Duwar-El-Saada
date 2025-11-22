<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ⭐ اجلب بيانات المستخدم */
$stmt = $pdo->prepare("
    SELECT u.role_id, u.username, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ⭐ تأكيد إنه admin */
if ($user['username'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit;
}

/* ⭐ استقبل البيانات من fetch POST */
$data = json_decode(file_get_contents("php://input"), true);

file_put_contents("debug.txt", print_r($data, true)); // 🔥 اختبر البيانات

$new_role_id = $data['role_id'] ?? null;

if ($new_role_id === null) {
    echo json_encode(['success' => false, 'message' => 'رقم الدور غير مُرسل']);
    exit;
}

/* ⭐ تأكد إن role_id فعلاً موجود */
$stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
$stmt->execute([$new_role_id]);
$role_name = $stmt->fetchColumn();

if (!$role_name) {
    echo json_encode(['success' => false, 'message' => 'الدور غير صالح']);
    exit;
}

/* ⭐ تحديث دور المستخدم */
$stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
$stmt->execute([$new_role_id, $user_id]);

/* ⭐ حفظ الدور الجديد في السيشن */
$_SESSION['current_role_id'] = $new_role_id;
$_SESSION['current_role'] = $role_name;

echo json_encode([
    'success' => true,
    'message' => 'تم تحديث الدور',
    'role_id' => $new_role_id,
    'role_name' => $role_name
]);
exit;