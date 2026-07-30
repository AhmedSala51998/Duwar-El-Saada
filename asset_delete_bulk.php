<?php
require __DIR__.'/config/config.php';
require_permission('assets.delete');

$ids = $_POST['ids'] ?? [];

if(empty($ids)){
    $_SESSION['toast'] = [
        'type' => 'warning',
        'msg'  => 'لم يتم تحديد أي عناصر'
    ];

    header('Location: ' . BASE_URL . '/assetes.php');
    exit;
}

$pdo->beginTransaction();

try {

    foreach($ids as $id){

        $id = (int)$id;

        $old = $pdo->prepare("SELECT * FROM assets WHERE id=?");
        $old->execute([$id]);
        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        if(!$oldData){
            continue;
        }

        if ($oldData['payment_source'] === 'عهدة') {

            $stmtTx = $pdo->prepare("
                SELECT *
                FROM custody_transactions
                WHERE type=? AND type_id=?
            ");

            $stmtTx->execute(['asset', $oldData['id']]);

            $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

            foreach($transactions as $tx){

                $stmtC = $pdo->prepare("
                    SELECT *
                    FROM custodies
                    WHERE id=?
                ");

                $stmtC->execute([$tx['custody_id']]);

                $custody = $stmtC->fetch(PDO::FETCH_ASSOC);

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
                WHERE type=? AND type_id=?
            ")->execute([
                'asset',
                $oldData['id']
            ]);
        }

        $pdo->prepare("
            DELETE FROM assets
            WHERE id=?
        ")->execute([$id]);
    }

    $pdo->commit();

    $_SESSION['toast'] = [
        'type' => 'success',
        'msg'  => 'تم حذف العناصر المحددة بنجاح'
    ];

} catch(Exception $e){

    $pdo->rollBack();

    $_SESSION['toast'] = [
        'type' => 'danger',
        'msg'  => $e->getMessage()
    ];
}

header('Location: ' . BASE_URL . '/assetes.php');
exit;