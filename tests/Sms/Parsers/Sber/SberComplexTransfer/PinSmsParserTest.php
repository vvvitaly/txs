<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\PinSmsParser;

/** @noinspection PhpMissingDocCommentInspection */

final class PinSmsParserTest extends TestCase
{
    /**
     * @return array
     */
    public function providerParseSms(): array
    {
        return [
            'sms transfer' => [
                self::sms(
                    '2019-09-01',
                    'Для перевода 455р получателю SOMEBODY на VISA0002 с карты VISA0001 отправьте код 27805 на 900. Комиссия не взимается'
                ),
                self::pin(
                    '2019-09-01',
                    'VISA0001',
                    455.0,
                    'р',
                    'Перевод SOMEBODY на VISA0002'
                ),
            ],
            'online transfer' => [
                self::sms(
                    '2019-09-02',
                    'Проверьте реквизиты перевода: карта списания **** 0001, карта зачисления **** 0002, сумма 1700,3 RUB. Пароль для подтверждения - 63659. Никому не сообщайте пароль.'
                ),
                self::pin(
                    '2019-09-02',
                    '0001',
                    1700.3,
                    'RUB',
                    'Перевод на ****0002'
                ),
            ],
            'confirm transfer' => [
                self::sms('2019-08-02', 'VISA0001 08:17 перевод 455р Баланс: 7673.22р'),
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
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage|null $expected
     *
     * @dataProvider providerParseSms
     */
    public function testParseSms(Message $sms, ?PinMessage $expected): void
    {
        $parser = new PinSmsParser();
        $this->assertEquals($expected, $parser->parseSms($sms));
    }

    /**
     * @param string $date
     * @param string $text
     *
     * @return \vvvitaly\txs\Sms\Message
     */
    private static function sms(string $date, string $text): Message
    {
        return new Message('900', new DateTimeImmutable($date), $text);
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $date
     * @param string $account
     * @param float $amount
     * @param string $currency
     * @param string $description
     *
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage
     */
    private static function pin(
        string $date,
        string $account,
        float $amount,
        string $currency,
        string $description
    ): PinMessage {
        $pin = new PinMessage();
        /** @noinspection PhpUnhandledExceptionInspection */
        $pin->receivingDate = new DateTimeImmutable($date);
        $pin->account = $account;
        $pin->amount = $amount;
        $pin->currency = $currency;
        $pin->description = $description;

        return $pin;
    }
}