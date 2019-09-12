<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Csv;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Csv\CsvReaderInterface;
use vvvitaly\txs\Csv\CsvReadException;
use vvvitaly\txs\Csv\Encode\CsvEncoderInterface;
use vvvitaly\txs\Csv\Encode\EncodeException;
use vvvitaly\txs\Csv\EncodeDecorator;

final class EncodeDecoratorTest extends TestCase
{
    public function testReadRow(): void
    {
        $originRow = [
            0,
            'col1',
            'col2',
            'col3',
        ];

        $originEncoding = 'windows-1251';

        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn($originRow);

        $encoder = $this->createMock(CsvEncoderInterface::class);
        $encoder->expects($this->exactly(3))
            ->method('encode')
            ->withConsecutive(
                [$originEncoding, 'col1'],
                [$originEncoding, 'col2'],
                [$originEncoding, 'col3']
            )
            ->willReturnOnConsecutiveCalls(
                'encoded_col1',
                'encoded_col2',
                'encoded_col3'
            );

        $decorator = new EncodeDecorator(
            $inner,
            $encoder,
            $originEncoding
        );

        $actual = $decorator->readRow();

        $this->assertSame(
            [
                0,
                'encoded_col1',
                'encoded_col2',
                'encoded_col3',
            ],
            $actual
        );
    }

    public function testReadRowNoRowsLeft(): void
    {
        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn(null);

        $encoder = $this->createMock(CsvEncoderInterface::class);
        $encoder->expects($this->never())
            ->method('encode');

        $actual = (new EncodeDecorator($inner, $encoder, 'windows-1251'))->readRow();

        $this->assertNull($actual);
    }

    public function testReadRowWithEncodingError(): void
    {
        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('readRow')
            ->willReturn(['row']);

        $encoder = $this->createMock(CsvEncoderInterface::class);
        $encoder->expects($this->once())
            ->method('encode')
            ->willThrowException(new EncodeException(__FUNCTION__));

        $reader = new EncodeDecorator($inner, $encoder, 'windows-1251');

        $this->expectException(CsvReadException::class);
        $reader->readRow();
    }

    public function testOpen(): void
    {
        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('open');

        $encoder = $this->createMock(CsvEncoderInterface::class);

        (new EncodeDecorator($inner, $encoder, 'windows-1251'))->open();
    }

    public function testClose(): void
    {
        $inner = $this->createMock(CsvReaderInterface::class);
        $inner->expects($this->once())
            ->method('close');

        $encoder = $this->createMock(CsvEncoderInterface::class);

        (new EncodeDecorator($inner, $encoder, 'windows-1251'))->close();
    }
}