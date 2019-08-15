<?php
/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection UnusedFunctionResultInspection */

declare(strict_types=1);

namespace tests\GnuCash\Export\Exporter;

use App\Bills\Amount;
use App\Bills\Bill;
use App\Bills\BillsCollection;
use App\GnuCash\Export\Contract\BillExporterInterface;
use App\GnuCash\Export\Data\Transaction;
use App\GnuCash\Export\Exporter\CollectionExporter;
use App\GnuCash\Export\Exporter\CollectionExportException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CollectionExporterTest extends TestCase
{
    public function testExport(): void
    {
        $bill1 = new Bill(new Amount(1));
        $tx1 = new Transaction();
        $tx1->id = 1;

        $bill2 = new Bill(new Amount(2));
        $tx2 = new Transaction();
        $tx2->id = 2;

        $inner = $this->createMock(BillExporterInterface::class);
        $inner->expects($this->exactly(2))
            ->method('exportBill')
            ->withConsecutive($bill1, $bill2)
            ->willReturnOnConsecutiveCalls(
                $tx1,
                $tx2
            );

        $txsList = (new CollectionExporter($inner))->export(new BillsCollection($bill1, $bill2));

        /** @var Transaction[] $txs */
        $txs = iterator_to_array($txsList, false);

        $this->assertCount(2, $txs);
        $this->assertEquals(1, $txs[0]->id);
        $this->assertEquals(2, $txs[1]->id);
    }

    public function testExportShouldStopOnException(): void
    {
        $bill1 = new Bill(new Amount(1));

        $inner = $this->createMock(BillExporterInterface::class);
        $inner->expects($this->once())
            ->method('exportBill')
            ->withConsecutive($bill1)
            ->willThrowException(new RuntimeException('test'));

        $exporter = new CollectionExporter($inner);

        $this->expectException(CollectionExportException::class);
        $exporter->export(new BillsCollection($bill1));
    }
}