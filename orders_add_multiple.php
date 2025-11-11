<?php 
require __DIR__ . '/config/config.php';
require_permission('orders.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {
    $orders = $_POST['orders'] ?? [];
    $successCount = 0;
    $errorCount = 0;

    foreach ($orders as $o) {
        $pid  = (int)($o['purchase_id'] ?? 0);
        $qty  = (float)($o['qty'] ?? 0);
        $unit = $o['unit'] ?? '';
        $note = trim($o['note'] ?? '');

        if ($pid <= 0 || $qty <= 0) continue;

        $p = $pdo->prepare("SELECT * FROM purchases WHERE id = ?");
        $p->execute([$pid]);
        $pr = $p->fetch();

        if (!$pr) { $errorCount++; continue; }

        $available = (float)$pr['quantity'];
        $punit = $pr['unit'];
        $need = $qty;

        // تحويل الوحدات
        if ($unit !== $punit) {
            if ($unit === 'جرام' && $punit === 'كيلو') { $need = $qty / 1000.0; }
            elseif ($unit === 'كيلو' && $punit === 'جرام') { $need = $qty * 1000.0; }
            elseif ($unit === 'مل' && $punit === 'لتر') { $need = $qty / 1000.0; }
            elseif ($unit === 'لتر' && $punit === 'مل') { $need = $qty * 1000.0; }
            else { $errorCount++; continue; }
        }

        if ($available >= $need) {
            try {
                $pdo->beginTransaction();

                $pdo->prepare("INSERT INTO orders (purchase_id, qty, unit, note) VALUES (?, ?, ?, ?)")
                    ->execute([$pid, $qty, $unit, $note]);

                $pdo->prepare("UPDATE purchases SET quantity = quantity - ? WHERE id = ?")
                    ->execute([$need, $pid]);

                $pdo->commit();
                $successCount++;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errorCount++;
            }
        } else {
            $errorCount++;
        }
    }

    $_SESSION['toast'] = [
        'type' => $errorCount === 0 ? 'success' : ($successCount > 0 ? 'warning' : 'danger'),
        'msg'  => "تم تنفيذ $successCount أمر بنجاح، وفشل $errorCount أمر."
    ];
}

header('Location: ' . BASE_URL . '/orders.php');
exit;
