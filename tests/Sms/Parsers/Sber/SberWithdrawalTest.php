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
use vvvitaly\txs\Sms\Parsers\Sber\SberWithdrawal;

final class SberWithdrawalTest extends SberSmsTestCase
{
    /**
     * @inheritDoc
     */
    protected function createParser(): MessageParserInterface
    {
        return new SberWithdrawal();
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'in atm' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA1111 10:06 Выдача 150000р OSB 9999 9999 Баланс: 68892.69р'),
                new Bill(
                    BillType::expense(),
                    new Amount(150000, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-01 10:06:00'), 'Выдача / р, OSB 9999 9999')
                ),
            ],
            'in bank' => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'),
                new Bill(
                    BillType::expense(),
                    new Amount(3400, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-03 11:31:00'), 'Выдача / р, ATM 00000001')
                ),
            ],
            'withdrawal with different date' => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA1111 01.08.19 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'),
                new Bill(
                    BillType::expense(),
                    new Amount(3400, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-01 11:31:00'), 'Выдача / р, ATM 00000001')
                ),
            ],
            'withdrawal, date only' => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA1111 01.08.19 Выдача 3400р ATM 00000001 Баланс: 16639.63р'),
                new Bill(
                    BillType::expense(),
                    new Amount(3400, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-01 00:00:00'), 'Выдача / р, ATM 00000001')
                ),
            ],
        ];
    }
}