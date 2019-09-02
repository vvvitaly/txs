<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use App\Exporters\BillExporter;
use App\Exporters\CollectionExporter;
use App\Exporters\CsvWriterConfig;
use App\Exporters\MultiSplitCsvWriter;
use App\Exporters\Processors\AutoIdCounter;
use App\Exporters\Processors\CompositeProcessor;
use App\Exporters\Processors\DescriptionAsAccount;

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
