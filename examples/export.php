<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use vvvitaly\txs\Exporters\BillExporter;
use vvvitaly\txs\Exporters\CollectionExporter;
use vvvitaly\txs\Exporters\CsvWriterConfig;
use vvvitaly\txs\Exporters\MultiSplitCsvWriter;
use vvvitaly\txs\Exporters\Processors\AutoIdCounter;
use vvvitaly\txs\Exporters\Processors\CompositeProcessor;
use vvvitaly\txs\Exporters\Processors\DescriptionAsAccount;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/debug.php';

if ($argc < 2) {
    echo "Usage: php {$argv[0]} [output file path]\n";
    exit(1);
}

$bills = randomBillsCollection(10);

$csvConfig = CsvWriterConfig::defaultConfig();
$file = new SplFileObject($argv[1], 'wb');

$billExporter = new BillExporter(
    new CompositeProcessor(
        new AutoIdCounter(),
        new DescriptionAsAccount()
    )
);
$exporter = new CollectionExporter($billExporter);

$csv = new MultiSplitCsvWriter($file, $csvConfig);

foreach ($bills as $bill) {
    echo dumpBill($bill) . "\n";
}

$csv->write($exporter->export($bills));

$fileName = $file->getRealPath();
echo "Bills were exported in $fileName\n";
