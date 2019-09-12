<?php

declare(strict_types=1);

namespace tests\Csv;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use vvvitaly\txs\Csv\CsvControl;
use vvvitaly\txs\Csv\SplFileObjectReader;

/** @noinspection PhpMissingDocCommentInspection */

final class SplFileObjectReaderTest extends TestCase
{
    public function testOpen(): void
    {
        $format = new CsvControl();
        $format->csvSeparator = ';';
        $format->enclosure = "'";
        $format->escape = '|';

        $file = $this->mockFile();
        $file->expects($this->once())
            ->method('setFlags')
            ->with(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->expects($this->once())
            ->method('setCsvControl')
            ->with(';', "'", '|');

        $reader = new SplFileObjectReader($file, $format);
        $reader->open();
    }

    public function testReadRow(): void
    {
        $row = ['col1', 'col2', 'col3'];

        $file = $this->mockFile();
        $file->expects($this->once())
            ->method('eof')
            ->willReturn(false);
        $file->expects($this->once())
            ->method('fgetcsv')
            ->willReturn($row);

        $reader = new SplFileObjectReader($file, new CsvControl());
        $reader->open();
        $actual = $reader->readRow();

        $this->assertSame($row, $actual);
    }

    public function testReadRowShouldReturnNullOnEnd(): void
    {
        $file = $this->mockFile();
        $file->expects($this->once())
            ->method('eof')
            ->willReturn(true);
        $file->expects($this->never())
            ->method('fgetcsv');

        $reader = new SplFileObjectReader($file, new CsvControl());
        $actual = $reader->readRow();

        $this->assertNull($actual);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SplFileObject
     */
    public function mockFile(): MockObject
    {
        return $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }
}