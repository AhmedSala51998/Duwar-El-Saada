<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // تحقق أولاً إذا في حركات على العهدة
    $stmt = $pdo->prepare("SELECT type FROM custody_transactions WHERE custody_id = ?");
    $stmt->execute([$id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($transactions)) {
        // في حركات مرتبطة بالعهدة، ما ينفع الحذف
        $types = array_unique($transactions);
        $translated = [];

        foreach ($types as $t) {
            switch ($t) {
                case 'purchase': $translated[] = 'مشتريات'; break;
                case 'expense':  $translated[] = 'مصروفات'; break;
                case 'asset':    $translated[] = 'أصول'; break;
                default:         $translated[] = $t;
            }
        }

        $_SESSION['toast'] = [
            'type' => 'error',
            'msg'  => 'لا يمكن حذف هذه العهدة لأنها تحتوي على حركات (' . implode(' - ', $translated) . ')'
        ];
    } else {
        // مفيش حركات، نحذف بأمان
        $pdo->prepare("DELETE FROM custodies WHERE id = ?")->execute([$id]);
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'تم حذف العهدة بنجاح'];
    }
}

header('Location: ' . BASE_URL . '/custodies.php');
exit;