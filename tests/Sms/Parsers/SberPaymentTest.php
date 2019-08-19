<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Sms\MessageParserInterface;
use App\Sms\Parsers\SberPayment;
use App\Sms\Sms;
use DateTimeImmutable;

final class SberPaymentTest extends SberSmsTestCase
{
    /**
     * @inheritDoc
     */
    protected function createParser(): MessageParserInterface
    {
        return new SberPayment();
    }

    /**
     * @inheritDoc
     */
    public function providerParseWrongAddress(): array
    {
        return [
            ['VISA1111 21:56 Оплата 610.10р Баланс: 21237.54р']
        ];
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'normal message' => [
                new Sms('900', new DateTimeImmutable('2019-08-01 23:01:13'), 'ECMC1234 02:38 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р'),
                new Bill(
                    new Amount(100, 'р'),
                    'ECMC1234',
                    new BillInfo(new DateTimeImmutable('2019-08-01 02:38:00'), 'TELE2 (9001234567)')
                )
            ],
            'normal message with different dates' => [
                new Sms('900', new DateTimeImmutable('2019-08-01 23:01:13'), 'ECMC1234 31.07.19 02:38 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р'),
                new Bill(
                    new Amount(100, 'р'),
                    'ECMC1234',
                    new BillInfo(new DateTimeImmutable('2019-07-31 02:38:00'), 'TELE2 (9001234567)')
                )
            ],
            'normal message, date only' => [
                new Sms('900', new DateTimeImmutable('2019-08-01 23:01:13'), 'ECMC1234 31.07.19 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р'),
                new Bill(
                    new Amount(100, 'р'),
                    'ECMC1234',
                    new BillInfo(new DateTimeImmutable('2019-07-31 00:00:00'), 'TELE2 (9001234567)')
                )
            ],
            'annual payment' => [
                new Sms('900', new DateTimeImmutable('2019-08-03 12:03:33'), 'VISA4321 03:06 оплата годового обслуживания карты 450.50р Баланс: 12345.62р'),
                new Bill(
                    new Amount(450.5, 'р'),
                    'VISA4321',
                    new BillInfo(new DateTimeImmutable('2019-08-03 03:06:0'), 'оплата годового обслуживания карты')
                )
            ],
            'mobile bank assistant' => [
                new Sms('900', new DateTimeImmutable('2019-08-03 12:03:33'), 'VISA2288 10:10 мобильный банк за 06.04-05.05 60р Баланс: 11227.69'),
                new Bill(
                    new Amount(60, 'р'),
                    'VISA2288',
                    new BillInfo(new DateTimeImmutable('2019-08-03 10:10:00'), 'мобильный банк')
                )
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function providerParseWrongBody(): array
    {
        return [
            'purchase sms' => ['VISA8413 20:46 Покупка 30р ENERGY POINT Баланс: 2261.20р'],
            'refill sms' => ['VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р'],
            'transfer sms' => ['С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'],
            'withdrawal sms' => ['VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'],
            'without description' => ['VISA1111 21:56 Оплата 610.10р Баланс: 21237.54р'],
            'pin sms' => ['Пароль для подтверждения платежа - 85596. Оплата 14240,00 RUB с карты **** 1111. Реквизиты: XXXXXX'],
        ];
    }
}