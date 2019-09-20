<?php

/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

abstract class SberSmsTestCase extends TestCase
{
    /**
     * @dataProvider providerParseWrongAddressWithCorrectMessage
     *
     * @param string $messageBody
     */
    public function testParseWrongAddress(string $messageBody): void
    {
        $sms = new Message('0', new DateTimeImmutable('now'), $messageBody);
        $this->assertNull($this->createParser()->parse($sms));
    }

    /**
     * @dataProvider providerParseWrongBody
     * @param string $messageBody
     */
    public function testParseWrongBody(string $messageBody): void
    {
        $sms = new Message('900', new DateTimeImmutable('now'), $messageBody);
        $this->assertNull($this->createParser()->parse($sms));
    }

    /**
     * @dataProvider providerParseRegularMessage
     */
    public function testParseRegularMessage(Message $sms, Bill $expectedBill): void
    {
        $this->assertEquals($expectedBill, $this->createParser()->parse($sms));
    }

    /**
     * Creates testing parser
     *
     * @return MessageParserInterface
     */
    abstract protected function createParser(): MessageParserInterface;

    /**
     * Data provider for testParseWrongAddress. Should provide specific SMS message.
     *
     * @return array
     * @see testParseWrongAddress
     */
    abstract public function providerParseWrongAddressWithCorrectMessage(): array;

    /**
     * Data provider for testParseWrongBody. Should provide specific SMS message that could not be parsed by testing parser.
     *
     * @return array
     * @see testParseWrongBody
     */
    abstract public function providerParseRegularMessage(): array;

    /**
     * Data provider for testParseRegularMessage. Should provide an instance of Sms class for parsing and instance
     * of Bill class which is expected after parsing.
     *
     * @return array
     * @see testParseRegularMessage
     */
    abstract public function providerParseWrongBody(): array;
}