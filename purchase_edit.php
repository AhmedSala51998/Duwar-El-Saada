<?php
require __DIR__.'/config/config.php';
require_role(['admin','manager']);

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $id = (int)$_POST['id'];
    $old = $pdo->prepare("SELECT * FROM purchases WHERE id=?");
    $old->execute([$id]); $oldData = $old->fetch(PDO::FETCH_ASSOC);

    $newData = [
        'name' => trim($_POST['name']),
        'quantity' => (float)($_POST['quantity'] ?? 0),
        'unit' => $_POST['unit'] ?? '',
        'price' => (float)($_POST['price'] ?? 0),
        'product_image' => upload_image('product_image') ?: $oldData['product_image'],
        'invoice_image' => upload_image('invoice_image') ?: $oldData['invoice_image'],
        'payer_name' => trim($_POST['payer_name'] ?? ''),
        'payment_source' => $_POST['payment_source'] ?? ''
    ];
    $total_new = $newData['quantity']*$newData['price'];
    $total_old = $oldData['quantity']*$oldData['price'];

    // إرجاع العهد القديم إذا كان الدفع من العهد
    if($oldData['payment_source']==='custody'){
        $stmt = $pdo->prepare("UPDATE custodies SET amount = amount + ? WHERE person_name=?");
        $stmt->execute([$total_old,$oldData['payer_name']]);
    }

    // خصم العهد الجديد
    $deducted = 0;
    if($newData['payment_source']==='custody'){
        $stmt = $pdo->prepare("SELECT * FROM custodies WHERE person_name=? AND amount>0 ORDER BY amount DESC LIMIT 1");
        $stmt->execute([$newData['payer_name']]); $custody = $stmt->fetch(PDO::FETCH_ASSOC);
        if($custody){
            $deducted = min($custody['amount'],$total_new);
            $pdo->prepare("UPDATE custodies SET amount = amount - ? WHERE person_name=?")->execute([$deducted,$newData['payer_name']]);
        }
    }

    $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE name=? AND unit=? AND payer_name=? AND id<>?");
    $check->execute([$newData['name'],$newData['unit'],$newData['payer_name'],$id]);
    if($check->fetchColumn()>0){
        $_SESSION['toast']=['type'=>'warning','msg'=>'هناك عملية شراء بنفس الاسم، الوحدة والدافع موجودة بالفعل'];
    } else {
        $pdo->prepare("UPDATE purchases SET name=?, quantity=?, unit=?, price=?, product_image=?, invoice_image=?, payer_name=?, payment_source=? WHERE id=?")
        ->execute([
            $newData['name'],
            $newData['quantity'],
            $newData['unit'],
            $newData['price'],
            $newData['product_image'],
            $newData['invoice_image'],
            $newData['payer_name'],
            $newData['payment_source'],
            $id
        ]);
        $_SESSION['toast']=['type'=>'success','msg'=>'تم تعديل العملية بنجاح'];
    }
}
header('Location: purchases.php'); exit;
