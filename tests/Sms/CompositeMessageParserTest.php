<?php

declare(strict_types=1);

namespace tests\Sms;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Sms\CompositeMessageParser;
use App\Sms\MessageParserInterface;
use App\Sms\Sms;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** @noinspection PhpMissingDocCommentInspection */

final class CompositeMessageParserTest extends TestCase
{
    public function testParse(): void
    {
        $sms = new Sms('test', new DateTimeImmutable('now'), 'test');
        $bill = new Bill(new Amount(1));

        $inner1 = $this->createMock(MessageParserInterface::class);
        $inner2 = $this->createMock(MessageParserInterface::class);
        $inner3 = $this->createMock(MessageParserInterface::class);

        $inner1->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $inner2->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo($sms))
            ->willReturn($bill);

        $inner3->expects($this->never())
            ->method('parse');

        $parser = new CompositeMessageParser($inner1, $inner2, $inner3);
        $actual = $parser->parse($sms);

        $this->assertSame($bill, $actual);
    }

    public function testParseShouldReturnNullIfNoParsersCanParse(): void
    {
        $sms = new Sms('test', new DateTimeImmutable('now'), 'test');

        $inner1 = $this->createMock(MessageParserInterface::class);
        $inner2 = $this->createMock(MessageParserInterface::class);

        $inner1->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $inner2->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $parser = new CompositeMessageParser($inner1, $inner2);
        $actual = $parser->parse($sms);

        $this->assertNull($actual);
    }
}