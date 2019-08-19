<?php

declare(strict_types=1);

namespace tests\Sms;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Sms\MessageParserInterface;
use App\Sms\Sms;
use App\Sms\SmsParser;
use App\Sms\SmsSourceInterface;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;

/** @noinspection PhpMissingDocCommentInspection */

final class SmsParserTest extends TestCase
{
    public function testParse(): void
    {
        $sms1 = new Sms('test', new DateTimeImmutable('now'), 'text1');
        $sms2 = new Sms('test', new DateTimeImmutable('now'), 'text2');
        $bill1 = new Bill(new Amount(1));
        $bill2 = new Bill(new Amount(2));

        $smsSource = $this->createMock(SmsSourceInterface::class);
        $smsSource->expects($this->once())
            ->method('read')
            ->willReturnOnConsecutiveCalls($this->arrayToGenerator([$sms1, $sms2]));

        $innerParser = $this->createMock(MessageParserInterface::class);
        $innerParser->expects($this->exactly(2))
            ->method('parse')
            ->withConsecutive($this->identicalTo($sms1), $this->identicalTo($sms2))
            ->willReturnOnConsecutiveCalls($bill1, $bill2);

        $parser = new SmsParser($smsSource, $innerParser);

        $actual = $parser->parse(new DateTimeImmutable('-1 year'), new DateTimeImmutable('now'));
        $actualList = iterator_to_array($actual, false);

        $this->assertCount(2, $actualList);
        $this->assertSame($bill1, $actualList[0]);
        $this->assertSame($bill2, $actualList[1]);
    }

    public function testParseShouldSkipSmsIfNoBill(): void
    {
        $sms1 = new Sms('test', new DateTimeImmutable('now'), 'text1');
        $sms2 = new Sms('test', new DateTimeImmutable('now'), 'text2');
        $bill2 = new Bill(new Amount(2));

        $smsSource = $this->createMock(SmsSourceInterface::class);
        $smsSource->expects($this->once())
            ->method('read')
            ->willReturnOnConsecutiveCalls($this->arrayToGenerator([$sms1, $sms2]));

        $innerParser = $this->createMock(MessageParserInterface::class);
        $innerParser->expects($this->exactly(2))
            ->method('parse')
            ->withConsecutive($this->identicalTo($sms1), $this->identicalTo($sms2))
            ->willReturnOnConsecutiveCalls(null, $bill2);

        $parser = new SmsParser($smsSource, $innerParser);

        $actual = $parser->parse(new DateTimeImmutable('-1 year'), new DateTimeImmutable('now'));
        $actualList = iterator_to_array($actual, false);

        $this->assertCount(1, $actualList);
        $this->assertSame($bill2, $actualList[0]);
    }

    /**
     * @param array $data
     *
     * @return Generator|null
     */
    private function arrayToGenerator(array $data): ?Generator
    {
        foreach ($data as $item) {
            yield $item;
        }
    }
}