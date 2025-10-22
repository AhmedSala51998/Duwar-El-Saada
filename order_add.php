<?php 
require __DIR__.'/config/config.php'; 
require_role(['admin','manager']);

if($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')){
    $pid = (int)$_POST['purchase_id']; 
    $qty = (float)$_POST['qty']; 
    $unit = $_POST['unit']; 
    $note = trim($_POST['note'] ?? '');

    $p = $pdo->prepare("SELECT * FROM purchases WHERE id=?"); 
    $p->execute([$pid]); 
    $pr = $p->fetch();

    if($pr){
        $available = (float)$pr['quantity']; 
        $punit = $pr['unit']; 
        $need = $qty;

        if($unit !== $punit){
            if($unit === 'جرام' && $punit === 'كيلو'){ 
                $need = $qty / 1000.0; 
            } elseif($unit === 'كيلو' && $punit === 'جرام'){ 
                $need = $qty * 1000.0; 
            }elseif($unit === 'مل' && $punit === 'لتر'){ 
                $need = $qty / 1000.0; 
            } elseif($unit === 'لتر' && $punit === 'مل'){ 
                $need = $qty * 1000.0; 
            } else { 
                flash('msg','لا يمكن التحويل بين هذه الوحدات. استخدم نفس وحدة المخزون.');
                header('Location: '.BASE_URL.'/orders.php'); 
                exit; 
            }
        }

        if($available >= $need){
            try {
                $pdo->beginTransaction();

                $pdo->prepare("INSERT INTO orders(purchase_id,qty,unit,note) VALUES(?,?,?,?)")
                    ->execute([$pid, $qty, $unit, $note]);

                $pdo->prepare("UPDATE purchases SET quantity = quantity - ? WHERE id=?")
                    ->execute([$need, $pid]);

                $pdo->commit();

                $_SESSION['toast'] = [
                    'type' => 'success',
                    'msg'  => 'تمت العملية بنجاح'
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                flash('msg','حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage());
            }
        } else {
            flash('msg','المخزون غير كافٍ');
        }
    }
}

header('Location: '.BASE_URL.'/orders.php');
