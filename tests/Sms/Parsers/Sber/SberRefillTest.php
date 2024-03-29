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
use vvvitaly\txs\Sms\Parsers\Sber\SberRefill;

final class SberRefillTest extends SberSmsTestCase
{
    /**
     * @inheritDoc
     */
    protected function createParser(): MessageParserInterface
    {
        return new SberRefill();
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'visa transfer' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р'),
                new Bill(
                    BillType::income(),
                    new Amount(70292.68, 'р'),
                    'VISA0001',
                    new BillInfo(new DateTimeImmutable('2019-08-01 10:06:00'),
                        'Зачисление VISA MONEY TRANSFER, VISA0001')
                ),
            ],
            'salary' => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA0001 16:30 зачисление зарплаты 35000р Баланс: 115795.17р'),
                new Bill(
                    BillType::income(),
                    new Amount(35000, 'р'),
                    'VISA0001',
                    new BillInfo(new DateTimeImmutable('2019-08-03 16:30:00'), 'зачисление зарплаты, VISA0001')
                ),
            ],
            'some payout' => [
                new Message('900', new DateTimeImmutable('2019-07-16 13:05:48'),
                    'VISA0001 19:58 зачисление страхового возмещения 6500р Баланс: 14763.42р'),
                new Bill(
                    BillType::income(),
                    new Amount(6500, 'р'),
                    'VISA0001',
                    new BillInfo(new DateTimeImmutable('2019-07-16 19:58:00'),
                        'зачисление страхового возмещения, VISA0001')
                ),
            ],
            'cash refill' => [
                new Message('900', new DateTimeImmutable('2019-07-29 10:10:28'),
                    'VISA0001 12:59 Зачисление 1000р ATM 60000111 Баланс: 10422.87р'),
                new Bill(
                    BillType::income(),
                    new Amount(1000, 'р'),
                    'VISA0001',
                    new BillInfo(new DateTimeImmutable('2019-07-29 12:59:00'), 'Зачисление ATM 60000111, VISA0001')
                ),
            ],
            'refill with full date' => [
                new Message('900', new DateTimeImmutable('2019-07-29 10:10:28'),
                    'VISA0001 28.07.19 12:59 Зачисление 1000р ATM 60000111 Баланс: 10422.87р'),
                new Bill(
                    BillType::income(),
                    new Amount(1000, 'р'),
                    'VISA0001',
                    new BillInfo(new DateTimeImmutable('2019-07-28 12:59:00'), 'Зачисление ATM 60000111, VISA0001')
                ),
            ],
            'refill, date only' => [
                new Message('900', new DateTimeImmutable('2019-07-29 10:10:28'),
                    'VISA0001 28.07.19 Зачисление 1000р ATM 60000111 Баланс: 10422.87р'),
                new Bill(
                    BillType::income(),
                    new Amount(1000, 'р'),
                    'VISA0001',
                    new BillInfo(new DateTimeImmutable('2019-07-28 00:00:00'), 'Зачисление ATM 60000111, VISA0001')
                ),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function providerParseWrongBody(): array
    {
        return array_merge(parent::providerParseWrongBody(), [
            'transfer between own accounts' => ['VISA7777 08:34 зачисление 200000р со вклада Баланс: 208892.69р'],
        ]);
    }
}