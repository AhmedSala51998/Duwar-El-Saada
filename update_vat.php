<?php require __DIR__.'/config/config.php'; // اتصال قاعدة البيانات 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $orderId = (int)($_POST['order_id'] ?? 0); 
    $vat = floatval($_POST['vat'] ?? 0); 
    $allTotal = floatval($_POST['all_total'] ?? 0); 
    if ($orderId > 0) { 
        $stmt = $pdo->prepare("UPDATE orders_purchases SET vat=?, all_total=? WHERE id=?"); 
        $stmt->execute([$vat, $allTotal, $orderId]); echo "VAT and total updated successfully"; 
        } else { 
            echo "Invalid order ID"; 
        } } ?>