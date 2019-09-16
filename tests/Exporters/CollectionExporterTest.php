<?php
/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection UnusedFunctionResultInspection */

declare(strict_types=1);

namespace tests\Exporters;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Exporters\CollectionExporter;
use vvvitaly\txs\Exporters\CollectionExportException;

final class CollectionExporterTest extends TestCase
{
    public function testExport(): void
    {
        $bill1 = new Bill(BillType::expense(), new Amount(1));
        $tx1 = new Transaction();
        $tx1->id = 1;

        $bill2 = new Bill(BillType::expense(), new Amount(2));
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
        $bill1 = new Bill(BillType::expense(), new Amount(1));

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