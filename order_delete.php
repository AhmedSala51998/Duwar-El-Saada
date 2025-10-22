<?php 
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);

if($id){
    $s = $pdo->prepare("SELECT * FROM orders WHERE id=?"); 
    $s->execute([$id]); 

    if($o = $s->fetch()){
        $p = $pdo->prepare("SELECT unit FROM purchases WHERE id=?"); 
        $p->execute([$o['purchase_id']]); 
        $pu = $p->fetch();

        if($pu){
            $restore = $o['qty']; 

            // تحويل الوحدات
            if($o['unit'] !== $pu['unit']){
                if($o['unit'] === 'جرام' && $pu['unit'] === 'كيلو') $restore = $o['qty'] / 1000.0; 
                elseif($o['unit'] === 'كيلو' && $pu['unit'] === 'جرام') $restore = $o['qty'] * 1000.0; 
                elseif($o['unit'] === 'مل' && $pu['unit'] === 'لتر') $restore = $o['qty'] / 1000.0; 
                elseif($o['unit'] === 'لتر' && $pu['unit'] === 'مل') $restore = $o['qty'] * 1000.0; 
                else {
                    flash('msg','لا يمكن التحويل بين هذه الوحدات.');
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
                    'msg'  => 'تمت العملية بنجاح'
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                flash('msg','حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage());
            }
        }
    }
}

header('Location: '.BASE_URL.'/orders.php');
