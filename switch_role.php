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

/* 🔥 اجلب الدور الحالي الصحيح */
$stmt = $pdo->prepare("
    SELECT r.name 
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$current_role = $stmt->fetchColumn();

if ($current_role !== 'مدير النظام') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit;
}

/* استقبل البيانات */
$input = json_decode(file_get_contents('php://input'), true);
$new_role = $input['role'] ?? '';

if (!$new_role) {
    echo json_encode(['success' => false, 'message' => 'الدور غير محدد']);
    exit;
}

/* تحقق إن الدور موجود */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute([$new_role]);

if ($stmt->fetchColumn() == 0) {
    echo json_encode(['success' => false, 'message' => 'الدور غير صالح']);
    exit;
}

/* حفظ الدور الجديد في السيشن */
$_SESSION['current_role'] = $new_role;

echo json_encode(['success' => true, 'role' => $new_role]);
exit;