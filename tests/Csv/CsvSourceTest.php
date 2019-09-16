<?php

declare(strict_types=1);

namespace tests\Csv;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Source\SourceReadException;
use vvvitaly\txs\Csv\CsvColumn;
use vvvitaly\txs\Csv\CsvReaderInterface;
use vvvitaly\txs\Csv\CsvReadException;
use vvvitaly\txs\Csv\CsvSource;

/** @noinspection PhpMissingDocCommentInspection */

final class CsvSourceTest extends TestCase
{
    public function testRead(): void
    {
        $columns = [
            CsvColumn::IGNORE,
            CsvColumn::DESCRIPTION,
            CsvColumn::ACCOUNT,
            CsvColumn::CURRENCY,
            CsvColumn::IGNORE,
            CsvColumn::AMOUNT,
            CsvColumn::DATE,
        ];
        $row = [
            '123', // column1
            'test description', // description
            'test', // account
            'USD', // currency
            '123', // column2
            '-123.456', // amount
            '2019-09-10 23:50:00', // date
        ];

        $reader = $this->createMock(CsvReaderInterface::class);
        $reader->expects($this->once())
            ->method('open');
        $reader->expects($this->exactly(2))
            ->method('readRow')
            ->willReturnOnConsecutiveCalls(
                $row,
                null
            );
        $reader->expects($this->once())
            ->method('close');

        $source = new CsvSource($columns, $reader);
        /** @var Bill[] $bills */
        $bills = iterator_to_array($source->read(), false);

        $this->assertCount(1, $bills);
        $this->assertTrue($bills[0]->isExpense());
        $this->assertEquals('test', $bills[0]->getAccount());
        $this->assertEquals(123.456, $bills[0]->getAmount()->getValue());
        $this->assertEquals('USD', $bills[0]->getAmount()->getCurrency());
        $this->assertEquals(new DateTimeImmutable('2019-09-10 23:50:00'), $bills[0]->getInfo()->getDate());
        $this->assertEquals('test description', $bills[0]->getInfo()->getDescription());
        $this->assertCount(0, $bills[0]->getItems());
    }

    public function testReadShouldSkipEmptyRows(): void
    {
        $columns = [
            CsvColumn::IGNORE,
            CsvColumn::DESCRIPTION,
            CsvColumn::ACCOUNT,
            CsvColumn::CURRENCY,
            CsvColumn::IGNORE,
            CsvColumn::AMOUNT,
            CsvColumn::DATE,
        ];

        $reader = $this->createMock(CsvReaderInterface::class);
        $reader->expects($this->exactly(2))
            ->method('readRow')
            ->willReturnOnConsecutiveCalls(
                [],
                null
            );

        $source = new CsvSource($columns, $reader);
        /** @var Bill[] $bills */
        $bills = iterator_to_array($source->read(), false);

        $this->assertCount(0, $bills);
    }

    public function testReadWithReaderError(): void
    {
        $columns = [
            CsvColumn::ACCOUNT,
            CsvColumn::AMOUNT,
            CsvColumn::DATE,
        ];

        $reader = $this->createMock(CsvReaderInterface::class);
        $reader->expects($this->once())
            ->method('open');
        $reader->expects($this->once())
            ->method('readRow')
            ->willThrowException(
                new CsvReadException(__FUNCTION__)
            );
        $reader->expects($this->once())
            ->method('close');

        $source = new CsvSource($columns, $reader);

        $this->expectException(SourceReadException::class);
        $source->read();
    }
}