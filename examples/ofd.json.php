<?php

declare(strict_types=1);

use vvvitaly\txs\Fdo\FdoJsonSource;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/debug.php';

if ($argc < 3) {
    echo "Usage: php {$argv[0]} [JSON file path] [account]\n";
    exit(1);
}

[, $reportFile, $defaultAccount] = $argv;

$json = json_decode(file_get_contents($reportFile), true);
$source = new FdoJsonSource($json, $defaultAccount);

foreach ($source->read() as $bill) {
    echo dumpBill($bill) . "\n";
}