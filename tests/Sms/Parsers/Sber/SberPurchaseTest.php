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
            'purchase, usd' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA1122 19:52 Покупка 8.90 USD XXXXX Баланс: 9502.93р'),
                new Bill(
                    BillType::expense(),
                    new Amount(8.9, 'USD'),
                    'VISA1122',
                    new BillInfo(new DateTimeImmutable('2019-08-01 19:52:00'), 'XXXXX')
                ),
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
}