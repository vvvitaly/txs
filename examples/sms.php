<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use vvvitaly\txs\Libs\Date\DatesRange;
use vvvitaly\txs\Sms\Parsers\Sber\SberParser;
use vvvitaly\txs\Sms\SmsBackupXMLSource;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/debug.php';

if ($argc < 3) {
    echo "Usage: php {$argv[0]} [XML file path] [date from]\n";
    exit(1);
}

[, $fileName, $dateFrom] = $argv;

$dates = new DatesRange(
    new DateTimeImmutable($dateFrom)
);
$xml = simplexml_load_string(file_get_contents($fileName));

// This kind of SMS contains account name that can be associated with GnuCash.
$source = new SmsBackupXMLSource($xml, $dates, new SberParser());

foreach ($source->read() as $bill) {
    echo dumpBill($bill) . "\n";
}
