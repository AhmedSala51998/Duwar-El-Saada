<?php
require __DIR__.'/config/config.php';
require_permission('custodies.add_group');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['_csrf'] ?? '')) {

    $custodies = $_POST['custodies'] ?? [];
    $lastSerial = $pdo->query("SELECT invoice_serial FROM custodies ORDER BY id DESC LIMIT 1")->fetchColumn();
    $nextNumber = 1;

    if ($lastSerial && preg_match('/DAELC(\d+)/', $lastSerial, $m)) {
        $nextNumber = (int)$m[1] + 1;
    }

    $stmt = $pdo->prepare("INSERT INTO custodies (invoice_serial, person_name, amount, main_amount, sub_amount, taken_at, notes)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($custodies as $c) {
        $person = trim($c['person_name'] ?? '');
        $amount = (float)($c['amount'] ?? 0);
        $taken  = trim($c['taken_at'] ?? '');
        $notes  = trim($c['notes'] ?? '');

        if ($person && $amount > 0 && $taken) {
            $serial_invoice = "DAELC" . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);
            $stmt->execute([$serial_invoice, $person, $amount, $amount, $amount, $taken, $notes]);
            $nextNumber++;
        }
    }

    $_SESSION['toast'] = ['type'=>'success', 'msg'=>'تمت إضافة العهد بنجاح'];
}

header('Location: '.BASE_URL.'/custodies.php');
exit;
