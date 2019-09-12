<?php

declare(strict_types=1);

namespace tests\Csv;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Csv\CsvReaderInterface;
use vvvitaly\txs\Csv\RowsFilterDecorator;

/** @noinspection PhpMissingDocCommentInspection */

final class RowsFilterDecoratorTest extends TestCase
{
    public function testReadRow(): void
    {
        $originRow = ['row'];

        $filter = static function () {
            return true;
        };

        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn($originRow);

        $decorator = new RowsFilterDecorator(
            $inner,
            $filter
        );

        $actual = $decorator->readRow();

        $this->assertSame($originRow, $actual);
    }

    public function testReadRowFiltered(): void
    {
        $originRow = ['row'];

        $filter = static function () {
            return false;
        };

        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn($originRow);

        $decorator = new RowsFilterDecorator(
            $inner,
            $filter
        );

        $actual = $decorator->readRow();

        $this->assertSame([], $actual);
    }

    public function testReadRowWithNull(): void
    {
        $filterCalled = false;
        $filter = static function () use (&$filterCalled) {
            $filterCalled = true;

            return false;
        };

        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn(null);

        $decorator = new RowsFilterDecorator(
            $inner,
            $filter
        );

        $actual = $decorator->readRow();

        $this->assertNull($actual);
        $this->assertFalse($filterCalled);
    }
}