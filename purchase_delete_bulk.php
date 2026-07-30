<?php

require __DIR__.'/config/config.php';
require_permission('purchases.delete');

$ids = $_POST['ids'] ?? [];

if(empty($ids)){

    $_SESSION['toast'] = [
        'type'=>'warning',
        'msg'=>'لم يتم تحديد أي عناصر'
    ];

    header('Location: '.BASE_URL.'/purchases.php');
    exit;
}

try{

    $pdo->beginTransaction();

    $deletedCount = 0;

    $blockedCount = 0;

    foreach($ids as $id){

        $id = (int)$id;

        $old = $pdo->prepare("
            SELECT *
            FROM purchases
            WHERE id=?
        ");

        $old->execute([$id]);

        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        if(!$oldData){
            continue;
        }

        $stmtIssued = $pdo->prepare("
            SELECT MAX(qty)
            FROM orders
            WHERE purchase_id=?
        ");

        $stmtIssued->execute([$id]);

        $maxIssuedQty =
            (float)$stmtIssued->fetchColumn();

        if($maxIssuedQty > 0){

            $blockedCount++;

            continue;
        }

        $orderId =
            $oldData['order_id'] ?? null;

        if($oldData['payment_source'] === 'عهدة'){

            $stmtTx = $pdo->prepare("
                SELECT *
                FROM custody_transactions
                WHERE type='purchase'
                AND type_id=?
            ");

            $stmtTx->execute([
                $oldData['id']
            ]);

            $transactions =
                $stmtTx->fetchAll(PDO::FETCH_ASSOC);

            foreach($transactions as $tx){

                $stmtC = $pdo->prepare("
                    SELECT *
                    FROM custodies
                    WHERE id=?
                ");

                $stmtC->execute([
                    $tx['custody_id']
                ]);

                $custody =
                    $stmtC->fetch(PDO::FETCH_ASSOC);

                if($custody){

                    $newAmount =
                        $custody['amount']
                        + $tx['amount'];

                    $pdo->prepare("
                        UPDATE custodies
                        SET amount=?
                        WHERE id=?
                    ")->execute([
                        $newAmount,
                        $custody['id']
                    ]);
                }
            }

            $pdo->prepare("
                DELETE FROM custody_transactions
                WHERE type=?
                AND type_id=?
            ")->execute([
                'purchase',
                $oldData['id']
            ]);
        }

        $pdo->prepare("
            DELETE FROM purchases
            WHERE id=?
        ")->execute([$id]);

        $deletedCount++;

        if($orderId){

            $stmtItems = $pdo->prepare("
                SELECT
                    total_packages,
                    total_price
                FROM purchases
                WHERE order_id=?
            ");

            $stmtItems->execute([$orderId]);

            $items =
                $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            if(count($items)==0){

                $pdo->prepare("
                    DELETE FROM orders_purchases
                    WHERE id=?
                ")->execute([$orderId]);

            }else{

                $total = 0;

                foreach($items as $item){

                    $total +=
                        $item['total_packages']
                        *
                        $item['total_price'];

                }

                $vat =
                    $total * 0.15;

                $allTotal =
                    $total + $vat;

                $pdo->prepare("
                    UPDATE orders_purchases
                    SET
                        total=?,
                        vat=?,
                        all_total=?
                    WHERE id=?
                ")->execute([
                    $total,
                    $vat,
                    $allTotal,
                    $orderId
                ]);
            }
        }
    }

    $pdo->commit();

    $_SESSION['toast'] = [

        'type'=>'success',

        'msg'=>

        "تم حذف {$deletedCount} عنصر بنجاح"

        .

        ($blockedCount
            ? " - {$blockedCount} عنصر لم يحذف لوجود إذن صرف"
            : ''
        )

    ];

}catch(Exception $e){

    $pdo->rollBack();

    $_SESSION['toast'] = [

        'type'=>'danger',

        'msg'=>$e->getMessage()

    ];
}

header('Location: '.BASE_URL.'/purchases.php');

exit;