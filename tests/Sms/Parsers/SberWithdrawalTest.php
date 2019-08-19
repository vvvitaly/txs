<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Sms\MessageParserInterface;
use App\Sms\Parsers\SberWithdrawal;
use App\Sms\Sms;
use DateTimeImmutable;

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
    public function providerParseWrongAddress(): array
    {
        return [
            ['VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'in atm' => [
                new Sms('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA1111 10:06 Выдача 150000р OSB 9999 9999 Баланс: 68892.69р'),
                new Bill(
                    new Amount(150000, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-01 10:06:00'), 'Выдача OSB 9999 9999')
                ),
            ],
            'in bank' => [
                new Sms('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'),
                new Bill(
                    new Amount(3400, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-03 11:31:00'), 'Выдача ATM 00000001')
                ),
            ],
            'withdrawal with different date' => [
                new Sms('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA1111 01.08.19 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'),
                new Bill(
                    new Amount(3400, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-01 11:31:00'), 'Выдача ATM 00000001')
                ),
            ],
            'withdrawal, date only' => [
                new Sms('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'VISA1111 01.08.19 Выдача 3400р ATM 00000001 Баланс: 16639.63р'),
                new Bill(
                    new Amount(3400, 'р'),
                    'VISA1111',
                    new BillInfo(new DateTimeImmutable('2019-08-01 00:00:00'), 'Выдача ATM 00000001')
                ),
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
            'purchase sms' => ['VISA8413 20:46 Покупка 30р ENERGY POINT Баланс: 2261.20р'],
            'refill sms' => ['VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р'],
            'transfer sms' => ['С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'],
        ];
    }
}