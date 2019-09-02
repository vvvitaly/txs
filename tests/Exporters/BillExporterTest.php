<?php
/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection UnusedFunctionResultInspection */

declare(strict_types=1);

namespace tests\Exporters;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillItem;
use vvvitaly\txs\Core\Export\InvalidBillException;
use vvvitaly\txs\Exporters\BillExporter;
use vvvitaly\txs\Exporters\Processors\ProcessorInterface;

final class BillExporterTest extends TestCase
{
    public function testExportBill(): void
    {
        $bill = new Bill(
            new Amount(1.2, 'RUB'),
            'deposit',
            new BillInfo(new DateTimeImmutable('2019-08-13'), 'monthly deposit', '#1')
        );

        $tx = (new BillExporter())->exportBill($bill);

        $this->assertEquals(new DateTimeImmutable('2019-08-13'), $tx->date);
        $this->assertEquals(null, $tx->id);
        $this->assertEquals('#1', $tx->num);
        $this->assertEquals('deposit', $tx->account);
        $this->assertEquals('monthly deposit', $tx->description);
        $this->assertEquals(-1.2, $tx->amount);
        $this->assertEquals('RUB', $tx->currency);

        $this->assertCount(1, $tx->splits);

        $this->assertEquals(1.2, $tx->splits[0]->amount);
        $this->assertEquals(null, $tx->splits[0]->memo);
        $this->assertEquals(null, $tx->splits[0]->account);
    }

    public function testExportBillWithItems(): void
    {
        $bill = new Bill(
            new Amount(1.2, 'RUB'),
            'cash',
            new BillInfo(new DateTimeImmutable('2019-08-13'), 'bill #1', '#1'),
            [
                new BillItem('buy #1', new Amount(1)),
                new BillItem('buy #2', new Amount(0.2)),
            ]
        );

        $tx = (new BillExporter())->exportBill($bill);

        $this->assertEquals(new DateTimeImmutable('2019-08-13'), $tx->date);
        $this->assertEquals(null, $tx->id);
        $this->assertEquals('#1', $tx->num);
        $this->assertEquals('cash', $tx->account);
        $this->assertEquals('bill #1', $tx->description);
        $this->assertEquals(-1.2, $tx->amount);
        $this->assertEquals('RUB', $tx->currency);

        $this->assertCount(2, $tx->splits);

        $this->assertEquals(1, $tx->splits[0]->amount);
        $this->assertEquals('buy #1', $tx->splits[0]->memo);
        $this->assertEquals(null, $tx->splits[0]->account);

        $this->assertEquals(0.2, $tx->splits[1]->amount);
        $this->assertEquals('buy #2', $tx->splits[1]->memo);
        $this->assertEquals(null, $tx->splits[1]->account);
    }

    public function testExportBillShouldExceptionIfNoDate(): void
    {
        $bill = new Bill(
            new Amount(1.2, 'RUB'),
            'deposit',
            new BillInfo(null, 'monthly deposit', '#1')
        );

        $this->expectException(InvalidBillException::class);
        (new BillExporter())->exportBill($bill);
    }

    public function testExportBillShouldExceptionIfNoAccount(): void
    {
        $bill = new Bill(
            new Amount(1.2, 'RUB'),
            null,
            new BillInfo(new DateTimeImmutable('2019-08-13'), 'monthly deposit', '#1')
        );

        $this->expectException(InvalidBillException::class);
        (new BillExporter())->exportBill($bill);
    }

    public function testExportBillShouldExceptionIfNoDescription(): void
    {
        $bill = new Bill(
            new Amount(1.2, 'RUB'),
            null,
            new BillInfo(new DateTimeImmutable('2019-08-13'), null, '#1')
        );

        $this->expectException(InvalidBillException::class);
        (new BillExporter())->exportBill($bill);
    }

    public function testExportBillWithProcessors(): void
    {
        $bill = new Bill(
            new Amount(1.2, 'RUB'),
            'deposit',
            new BillInfo(new DateTimeImmutable('2019-08-13'), 'monthly deposit', '#1')
        );

        $processor = $this->createMock(ProcessorInterface::class);
        $processor
            ->expects($this->once())
            ->method('process');

        (new BillExporter($processor))->exportBill($bill);
    }
}