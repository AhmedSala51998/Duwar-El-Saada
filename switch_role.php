<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require __DIR__ . '/config/config.php';

// التحقق من أن المستخدم مسجل الدخول
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit;
}

$user_id = $_SESSION['user_id'];

// تحقق من أن المستخدم أدمن
$stmt = $pdo->prepare("SELECT r.name 
                       FROM users u
                       JOIN roles r ON r.id = u.role_id
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$current_role = $stmt->fetchColumn();

if ($current_role !== 'مدير المظام') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit;
}

// استقبال البيانات من AJAX
$input = json_decode(file_get_contents('php://input'), true);
$new_role = $input['role'] ?? '';

if (!$new_role) {
    echo json_encode(['success' => false, 'message' => 'الدور غير محدد']);
    exit;
}

// تحقق أن الدور موجود فعليًا في قاعدة البيانات
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute([$new_role]);
if ($stmt->fetchColumn() == 0) {
    echo json_encode(['success' => false, 'message' => 'الدور غير صالح']);
    exit;
}

// حدث الدور في الجلسة فقط (أو حسب احتياجك يمكن تغييره في قاعدة البيانات)
$_SESSION['current_role'] = $new_role;

echo json_encode(['success' => true]);
exit;
