<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\ComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\ConfirmationSmsParser;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;

/** @noinspection PhpMissingDocCommentInspection */

final class TransferSmsParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestIncomplete();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function providerParseSms(): array
    {
        return [
            'transfer, time' => [
                self::sms(
                    '2019-09-01',
                    'VISA0001 08:17 перевод 455р Баланс: 7673.22р'
                ),
                self::transfer(
                    '2019-09-01',
                    '2019-09-01 08:17:00',
                    'VISA0001',
                    455.0
                ),
            ],
            'transfer, date & time' => [
                self::sms(
                    '2019-09-01',
                    'VISA0001 27.09.19 08:17 перевод 455р Баланс: 7673.22р'
                ),
                self::transfer(
                    '2019-09-01',
                    '2019-09-27 08:17:00',
                    'VISA0001',
                    455.0
                ),
            ],
            'transfer, only date' => [
                self::sms(
                    '2019-09-01',
                    'VISA0001 27.09.19 перевод 455р Баланс: 7673.22р'
                ),
                self::transfer(
                    '2019-09-01',
                    '2019-09-27 00:00:00',
                    'VISA0001',
                    455.0
                ),
            ],
            'pin' => [
                self::sms(
                    '2019-09-02',
                    'Проверьте реквизиты перевода: карта списания **** 0001, карта зачисления **** 0002, сумма 1700,3 RUB. Пароль для подтверждения - 63659. Никому не сообщайте пароль.'
                ),
                null,
            ],
            'wrong sms type' => [
                self::sms('2019-08-02', 'ECMC1234 02:38 Оплата 100р TELE2 Баланс: 14074.22р'),
                null,
            ],
            'simple transfer' => [
                self::sms('2019-08-02',
                    'С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'),
                null,
            ],
        ];
    }

    /**
     * @param \vvvitaly\txs\Sms\Message $sms
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage|null $expected
     *
     * @dataProvider providerParseSms
     */
    public function testParseSms(Message $sms, ?ConfirmationMessage $expected): void
    {
        $parser = new ConfirmationSmsParser();
        $this->assertEquals($expected, $parser->parseSms($sms));
    }

    /**
     * @param string $date
     * @param string $text
     *
     * @return \vvvitaly\txs\Sms\Message
     * @throws \Exception
     */
    private static function sms(string $date, string $text): Message
    {
        return new Message('900', new DateTimeImmutable($date), $text);
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $smsDate
     * @param string $realDate
     * @param string $account
     * @param float $amount
     *
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage
     * @throws \Exception
     */
    private static function transfer(
        string $smsDate,
        string $realDate,
        string $account,
        float $amount
    ): ConfirmationMessage
    {
        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = new DateTimeImmutable($smsDate);
        $transfer->transferDate = new DateTimeImmutable($realDate);
        $transfer->account = $account;
        $transfer->amount = $amount;

        return $transfer;
    }
}