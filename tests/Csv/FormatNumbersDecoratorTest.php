<?php

declare(strict_types=1);

namespace tests\Csv;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Csv\CsvReaderInterface;
use vvvitaly\txs\Csv\FormatNumbersDecorator;

/** @noinspection PhpMissingDocCommentInspection */

final class FormatNumbersDecoratorTest extends TestCase
{
    public function testReadRow(): void
    {
        $originRow = [
            'col1',
            'text with number 5000,00',
            '1234,23 and text',
            '5000,00',
            '1234,23',
            '-321,22',
        ];

        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn($originRow);

        $decorator = new FormatNumbersDecorator($inner);

        $actual = $decorator->readRow();

        $this->assertSame(
            [
                'col1',
                'text with number 5000,00',
                '1234,23 and text',
                '5000.00',
                '1234.23',
                '-321.22',
            ],
            $actual
        );
    }

    public function testReadRowWithNull(): void
    {
        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn(null);

        $decorator = new FormatNumbersDecorator($inner);

        $actual = $decorator->readRow();

        $this->assertNull($actual);
    }
}