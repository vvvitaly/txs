<?php

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
use vvvitaly\txs\Sms\Parsers\Sber\SberPurchase;

final class SberPurchaseTest extends SberSmsTestCase
{
    /**
     * @inheritDoc
     */
    protected function createParser(): MessageParserInterface
    {
        return new SberPurchase();
    }

    /**
     * @inheritDoc
     */
    public function providerParseWrongAddressWithCorrectMessage(): array
    {
        return [
            ['VISA1111 20:46 Покупка 1230.22р XXXX YYY Баланс: 2261.20р']
        ];
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'purchase' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA8413 20:46 Покупка 30р ENERGY POINT Баланс: 2261.20р'),
                new Bill(
                    BillType::expense(),
                    new Amount(30, 'р'),
                    'VISA8413',
                    new BillInfo(new DateTimeImmutable('2019-08-01 20:46:00'), 'ENERGY POINT')
                )
            ],
            'purchase with different dates' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA9009 31.07.19 20:38 Покупка 256.51р XXXXX YY Баланс: 11905.22р'),
                new Bill(
                    BillType::expense(),
                    new Amount(256.51, 'р'),
                    'VISA9009',
                    new BillInfo(new DateTimeImmutable('2019-07-31 20:38:00'), 'XXXXX YY')
                )
            ],
            'purcahse, date only' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA9009 31.07.19 Покупка 256.51р XXXXX YY Баланс: 11905.22р'),
                new Bill(
                    BillType::expense(),
                    new Amount(256.51, 'р'),
                    'VISA9009',
                    new BillInfo(new DateTimeImmutable('2019-07-31 00:00:00'), 'XXXXX YY')
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
            'payment sms' => ['VISA1111 21:56 Оплата 610.10р Баланс: 21237.54р'],
            'refill sms' => ['VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р'],
            'transfer sms' => ['С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'],
            'withdrawal sms' => ['VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'],
        ];
    }
}