<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber;

use DateTimeImmutable;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberPayment;

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
    public function providerParseRegularMessage(): array
    {
        return [
            'regular payment' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'ECMC1234 02:38 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р'),
                new Bill(
                    BillType::expense(),
                    new Amount(100, 'р'),
                    'ECMC1234',
                    new BillInfo(new DateTimeImmutable('2019-08-01 02:38:00'), 'Оплата TELE2 (9001234567)')
                )
            ],
            'regular payment with full date' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'ECMC1234 31.07.19 02:38 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р'),
                new Bill(
                    BillType::expense(),
                    new Amount(100, 'р'),
                    'ECMC1234',
                    new BillInfo(new DateTimeImmutable('2019-07-31 02:38:00'), 'Оплата TELE2 (9001234567)')
                )
            ],
            'regular payment, date only' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'ECMC1234 31.07.19 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р'),
                new Bill(
                    BillType::expense(),
                    new Amount(100, 'р'),
                    'ECMC1234',
                    new BillInfo(new DateTimeImmutable('2019-07-31 00:00:00'), 'Оплата TELE2 (9001234567)')
                )
            ],
            'annual payment' => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA4321 03:06 оплата годового обслуживания карты 450.50р Баланс: 12345.62р'),
                new Bill(
                    BillType::expense(),
                    new Amount(450.5, 'р'),
                    'VISA4321',
                    new BillInfo(new DateTimeImmutable('2019-08-03 03:06:0'), 'оплата годового обслуживания карты')
                )
            ],
            'mobile bank assistant' => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA2288 10:10 мобильный банк за 06.04-05.05 60р Баланс: 11227.69'),
                new Bill(
                    BillType::expense(),
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
        return array_merge(parent::providerParseWrongBody(), [
            'without description' => ['VISA1111 21:56 Оплата 610.10р Баланс: 21237.54р'],
            'pin sms' => ['Пароль для подтверждения платежа - 85596. Оплата 14240,00 RUB с карты **** 1111. Реквизиты: XXXXXX'],
        ]);
    }
}