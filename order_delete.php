<?php 
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $s = $pdo->prepare("SELECT * FROM orders WHERE id=?"); 
    $s->execute([$id]); 

    if ($o = $s->fetch()) {
        $p = $pdo->prepare("SELECT unit FROM purchases WHERE id=?"); 
        $p->execute([$o['purchase_id']]); 
        $pu = $p->fetch();

        if ($pu) {
            $restore = $o['qty']; 

            // تحويل الوحدات إذا لزم الأمر
            if ($o['unit'] !== $pu['unit']) {
                if ($o['unit'] === 'جرام' && $pu['unit'] === 'كيلو') $restore = $o['qty'] / 1000.0; 
                elseif ($o['unit'] === 'كيلو' && $pu['unit'] === 'جرام') $restore = $o['qty'] * 1000.0; 
                elseif ($o['unit'] === 'مل' && $pu['unit'] === 'لتر') $restore = $o['qty'] / 1000.0; 
                elseif ($o['unit'] === 'لتر' && $pu['unit'] === 'مل') $restore = $o['qty'] * 1000.0; 
                else {
                    $_SESSION['toast'] = [
                        'type' => 'danger',
                        'msg'  => 'لا يمكن التحويل بين هذه الوحدات.'
                    ];
                    header('Location: '.BASE_URL.'/orders.php'); 
                    exit;
                }
            }

            try {
                $pdo->beginTransaction();

                $pdo->prepare("UPDATE purchases SET quantity = quantity + ? WHERE id=?")
                    ->execute([$restore, $o['purchase_id']]);

                $pdo->prepare("DELETE FROM orders WHERE id=?")
                    ->execute([$id]);

                $pdo->commit();

                $_SESSION['toast'] = [
                    'type' => 'success',
                    'msg'  => 'تمت العملية بنجاح ✅'
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'msg'  => 'حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage()
                ];
            }
        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'msg'  => 'الوحدة غير موجودة في المخزون.'
            ];
        }
    } else {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'msg'  => 'الطلب غير موجود.'
        ];
    }
} else {
    $_SESSION['toast'] = [
        'type' => 'warning',
        'msg'  => 'لم يتم تحديد الطلب.'
    ];
}

header('Location: '.BASE_URL.'/orders.php');
exit;
