<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Regex;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Regex\MatcherInterface;
use vvvitaly\txs\Sms\Parsers\Regex\RegexParser;

final class RegexParserTest extends TestCase
{
    public function testParseMatched(): void
    {
        $text = 'some text';
        $matches = [];
        $sms = new Message('XXX', new DateTimeImmutable(), $text);
        $bill = new Bill(BillType::expense(), new Amount(123.4), 'test');

        $factory = function (Message $actualSms, array $actualMatches) use ($sms, $matches, $bill) {
            $this->assertSame($sms, $actualSms);
            $this->assertSame($matches, $actualMatches);

            return $bill;
        };

        $regexp = $this->createMock(MatcherInterface::class);
        $regexp->expects($this->once())
            ->method('match')
            ->with($text)
            ->willReturn($matches);

        $parser = new RegexParser(
            $regexp,
            $factory
        );

        $actual = $parser->parse($sms);

        $this->assertSame($bill, $actual);
    }

    public function testParseNotMatched(): void
    {
        $text = 'some text';
        $sms = new Message('XXX', new DateTimeImmutable(), $text);
        $bill = new Bill(BillType::expense(), new Amount(123.4), 'test');

        $called = false;
        $factory = static function () use (&$called, $bill) {
            $called = true;

            return $bill;
        };

        $regexp = $this->createMock(MatcherInterface::class);
        $regexp->expects($this->once())
            ->method('match')
            ->with($text)
            ->willReturn(null);

        $parser = new RegexParser(
            $regexp,
            $factory
        );

        $actual = $parser->parse($sms);

        $this->assertNull($actual);
        $this->assertFalse($called);
    }
}