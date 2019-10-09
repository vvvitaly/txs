<?php

/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

abstract class SberSmsTestCase extends TestCase
{
    /**
     * @var MessageParserInterface
     */
    private $parser;

    /**
     * @var string
     */
    private $parserType;

    /**
     * Data provider for testParseWrongBody.
     * It provides an instance of Sms class for parsing and instance of Bill class which is expected after parsing.
     *
     * @return array
     * @see testParseWrongBody
     */
    abstract public function providerParseRegularMessage(): array;

    /**
     * @dataProvider providerParseWrongBody
     *
     * @param string $messageBody
     */
    public function testParseWrongBody(string $messageBody): void
    {
        $sms = new Message('900', new DateTimeImmutable('now'), $messageBody);
        $this->assertNull($this->getParser()->parse($sms));
    }

    /**
     * @return MessageParserInterface
     */
    private function getParser(): MessageParserInterface
    {
        if ($this->parser === null) {
            $this->initParser();
        }

        return $this->parser;
    }

    /**
     * @dataProvider providerParseRegularMessage
     */
    public function testParseRegularMessage(Message $sms, Bill $expectedBill): void
    {
        $this->assertEquals($expectedBill, $this->getParser()->parse($sms));
    }

    /**
     * Data provider for testParseRegularMessage. It should return examples of messages which are not appropriate for
     * testing parser.
     *
     * @return array
     * @see testParseRegularMessage
     */
    public function providerParseWrongBody(): array
    {
        $messages = $this->getCorrectMessagesExamples();
        unset($messages[$this->getParserType()]);

        return $this->arrayToDataProvider($messages);
    }

    /**
     * Convert the given data to the data provider format.
     *
     * @param array $data
     *
     * @return array
     */
    private function arrayToDataProvider(array $data): array
    {
        return array_map(
            static function ($v) {
                return (array)$v;
            },
            $data
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $type = $this->getParserType();
        if (!isset($this->getCorrectMessagesExamples()[$type])) {
            throw new RuntimeException("Parser \"$type\" is not configured: correct message is not set");
        }
    }

    /**
     * @return string
     */
    private function getParserType(): string
    {
        if ($this->parserType === null) {
            $this->initParser();
        }

        return $this->parserType;
    }

    /**
     * Create parser and class
     */
    private function initParser(): void
    {
        $this->parser = $this->createParser();

        /** @noinspection PhpUnhandledExceptionInspection */
        $reflection = new ReflectionClass($this->parser);
        $this->parserType = $reflection->getShortName();
    }

    /**
     * Creates testing parser
     *
     * @return MessageParserInterface
     */
    abstract protected function createParser(): MessageParserInterface;

    public function getCorrectMessagesExamples(): array
    {
        return [
            'SberPayment' => 'ECMC1234 02:38 Оплата 100р TELE2 Баланс: 14074.22р',
            'SberRefill' => 'VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р',
            'SberTransfer' => 'С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.',
            'SberWithdrawal' => 'VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р',
            'SberPurchase' => 'VISA1111 20:46 Покупка 1230.22р XXXX YYY Баланс: 2261.20р',
            'SberRefund' => 'VISA1234 16.07.19 возврат покупки 111.09р XXXXX Баланс: 14867.80р',
        ];
    }
}