<?php

// يستدعى من ملفي الإضافة والتعديل بعد تحميل config.php.
function dhimam_input(): array
{
    $name = trim((string)($_POST['person_name'] ?? ''));
    $amount = trim((string)($_POST['amount'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $date = trim((string)($_POST['received_at'] ?? ''));

    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    if (
        $name === '' ||
        mb_strlen($name) > 255 ||
        $description === '' ||
        !preg_match(
            '/^(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D',
            $amount
        ) ||
        (float)$amount <= 0 ||
        !$parsedDate ||
        $parsedDate->format('Y-m-d') !== $date
    ) {
        throw new InvalidArgumentException(
            'يرجى إدخال الاسم والمبلغ الصحيح والبيان وتاريخ الاستلام.'
        );
    }

    return [$name, $amount, $description, $date];
}