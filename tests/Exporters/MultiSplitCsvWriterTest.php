<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters;

use App\Core\Export\Data\TransactionCollection;
use App\Exporters\CsvWriterConfig;
use App\Exporters\MultiSplitCsvWriter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use tests\Helpers\TransactionHelper;

final class MultiSplitCsvWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $singleTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-13'),
            'id' => 'tx1',
            'num' => '#1',
            'account' => 'cc',
            'description' => 'credit payment',
            'amount' => -100.12,
            'currency' => 'RUB',
            'splits' => [
                [
                    'amount' => 100.12,
                    'account' => 'expense:credit',
                ]
            ],
        ]);

        $splitTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-15'),
            'id' => 'tx2',
            'num' => '#2',
            'account' => 'cash',
            'description' => 'shopping',
            'amount' => -123.45,
            'splits' => [
                [
                    'amount' => 40,
                    'account' => 'expense:tomatoes',
                    'memo' => 'tomatoes'
                ],
                [
                    'amount' => 60,
                    'memo' => 'apples'
                ],
                [
                    'amount' => 23.45,
                    'memo' => 'coffee',
                ]
            ],
        ]);

        $expectedCsv = <<<EOS
Date,TxID,"Bill No",Account,"Tx Desc",Amount,Currency,Memo
2019-08-13,tx1,#1,cc,"credit payment",-100.12,RUB,
,tx1,,expense:credit,,100.12,,
2019-08-15,tx2,#2,cash,shopping,-123.45,,
,tx2,,expense:tomatoes,,40.00,,tomatoes
,tx2,,,,60.00,,apples
,tx2,,,,23.45,,coffee

EOS;

        $file = new SplFileObject('php://memory', 'rw+');

        $csv = new MultiSplitCsvWriter($file);
        $csv->write(new TransactionCollection($singleTx, $splitTx));

        $file->rewind();
        $contents = implode('', iterator_to_array($file, false));

        $this->assertEquals($expectedCsv, $contents);
    }

    public function testWriteWithConfiguration(): void
    {
        $singleTx = TransactionHelper::createTransaction([
            'date' => new DateTimeImmutable('2019-08-13'),
            'id' => 'tx1',
            'num' => '#1',
            'account' => 'cc',
            'description' => 'credit payment',
            'amount' => -100.12,
            'currency' => 'RUB',
            'splits' => [
                [
                    'amount' => 100.12,
                    'account' => 'expense:credit',
                ]
            ],
        ]);

        $config = new CsvWriterConfig();
        $config->dateFormat = 'n/d/Y';
        $config->decimalDelimiter = ',';
        $config->withHeader = false;
        $config->csvSeparator = ';';

        $expectedCsv = <<<EOS
8/13/2019;tx1;#1;cc;"credit payment";-100,12;RUB;
;tx1;;expense:credit;;100,12;;

EOS;

        $file = new SplFileObject('php://memory', 'rw+');

        $csv = new MultiSplitCsvWriter($file, $config);
        $csv->write(new TransactionCollection($singleTx));

        $file->rewind();
        $contents = implode('', iterator_to_array($file, false));

        $this->assertEquals($expectedCsv, $contents);
    }
}