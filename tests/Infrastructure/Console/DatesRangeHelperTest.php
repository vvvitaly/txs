<?php

declare(strict_types=1);

namespace tests\Infrastructure\Console;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Infrastructure\Console\DatesRangeHelper;

/** @noinspection PhpMissingDocCommentInspection */

final class DatesRangeHelperTest extends TestCase
{
    public function testParseFullPeriod(): void
    {
        $actual = (new DatesRangeHelper())->parseDates('2019-01-01  :  01-02-2019');
        $this->assertEquals(new DateTimeImmutable('2019-01-01'), $actual->getBegin());
        $this->assertEquals(new DateTimeImmutable('2019-02-01'), $actual->getEnd());
    }

    public function testParseWithoutBeginning(): void
    {
        $actual = (new DatesRangeHelper())->parseDates('  :  01-02-2019');
        $this->assertNull($actual->getBegin());
        $this->assertEquals(new DateTimeImmutable('2019-02-01'), $actual->getEnd());
    }

    public function testParseWithoutEnding(): void
    {
        $actual = (new DatesRangeHelper())->parseDates('2019-01-01  :');
        $this->assertEquals(new DateTimeImmutable('2019-01-01'), $actual->getBegin());
        $this->assertNull($actual->getEnd());
    }

    public function testParseExactlyDate(): void
    {
        $actual = (new DatesRangeHelper())->parseDates('2019-01-01');
        $this->assertEquals(new DateTimeImmutable('2019-01-01'), $actual->getBegin());
        $this->assertEquals(new DateTimeImmutable('2019-01-01'), $actual->getEnd());
    }

    public function testParseMissingDates(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new DatesRangeHelper())->parseDates(' :  ');
    }

    public function testParseWrongFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new DatesRangeHelper())->parseDates('1 :  2');
    }
}