<?php
require __DIR__.'/config/config.php';

require_permission('expenses.delete');

$ids = $_POST['ids'] ?? [];

if(empty($ids)){
    $_SESSION['toast'] = [
        'type'=>'danger',
        'msg'=>'لم يتم تحديد أي عنصر'
    ];

    header('Location: '.BASE_URL.'/expenses.php');
    exit;
}

try{

    $pdo->beginTransaction();

    foreach($ids as $id){

        $id = (int)$id;

        $old = $pdo->prepare("
            SELECT *
            FROM expenses
            WHERE id=?
        ");

        $old->execute([$id]);

        $oldData = $old->fetch(PDO::FETCH_ASSOC);

        if(!$oldData){
            continue;
        }

        if($oldData['payment_source'] === 'عهدة'){

            $stmtTx = $pdo->prepare("
                SELECT *
                FROM custody_transactions
                WHERE type='expense'
                AND type_id=?
            ");

            $stmtTx->execute([$id]);

            $transactions =
                $stmtTx->fetchAll(PDO::FETCH_ASSOC);

            foreach($transactions as $tx){

                $stmtC = $pdo->prepare("
                    SELECT *
                    FROM custodies
                    WHERE id=?
                ");

                $stmtC->execute([$tx['custody_id']]);

                $custody = $stmtC->fetch();

                if($custody){

                    $newAmount =
                        $custody['amount'] + $tx['amount'];

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
                WHERE type='expense'
                AND type_id=?
            ")->execute([$id]);
        }

        $pdo->prepare("
            DELETE FROM expenses
            WHERE id=?
        ")->execute([$id]);

        require_once __DIR__.'/libs/activity_log.php';

        add_activity_log(
            $pdo,
            'delete',
            'expenses',
            $id,
            "حذف مصروفات"
        );
    }

    $pdo->commit();

    $_SESSION['toast'] = [
        'type'=>'success',
        'msg'=>'تم حذف العناصر المحددة بنجاح'
    ];

}catch(Exception $e){

    $pdo->rollBack();

    $_SESSION['toast'] = [
        'type'=>'danger',
        'msg'=>$e->getMessage()
    ];
}

header('Location: '.BASE_URL.'/expenses.php');
exit;