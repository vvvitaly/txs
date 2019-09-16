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
use vvvitaly\txs\Sms\Parsers\Sber\SberTransfer;

final class SberTransferTest extends SberSmsTestCase
{
    /**
     * @inheritDoc
     */
    protected function createParser(): MessageParserInterface
    {
        return new SberTransfer();
    }

    /**
     * @inheritDoc
     */
    public function providerParseWrongAddress(): array
    {
        return [
            ['С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'card => account'   => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'),
                new Bill(
                    BillType::expense(),
                    new Amount(430, 'RUB'),
                    '1234',
                    new BillInfo(new DateTimeImmutable('2019-08-01 23:01:13'), 'Перевод на 10000000000000000123')
                ),
            ],
            'account => card'   => [
                new Message('900', new DateTimeImmutable('2019-08-03 12:03:33'),
                    'С Вашего счета 11111111111111111857 произведен перевод на карту № **** 4321 на сумму 19000,00 RUB.'),
                new Bill(
                    BillType::expense(),
                    new Amount(19000, 'RUB'),
                    '11111111111111111857',
                    new BillInfo(new DateTimeImmutable('2019-08-03 12:03:33'), 'Перевод на 4321')
                ),
            ],
            'card => card'      => [
                new Message('900', new DateTimeImmutable('2019-07-16 13:05:48'),
                    'С Вашей карты **** 7777 произведен перевод на карту № **** 0001 на сумму 6154,33 RUB.'),
                new Bill(
                    BillType::expense(),
                    new Amount(6154.33, 'RUB'),
                    '7777',
                    new BillInfo(new DateTimeImmutable('2019-07-16 13:05:48'), 'Перевод на 0001')
                ),
            ],
            'another transfers' => [
                new Message('900', new DateTimeImmutable('2019-07-16 13:05:48'),
                    'VISA1234 07:44 перевод 5000р TINKOFF Баланс: 3602.03р'),
                new Bill(
                    BillType::expense(),
                    new Amount(5000, 'р'),
                    'VISA1234',
                    new BillInfo(new DateTimeImmutable('2019-07-16 07:44:00'), 'Перевод на TINKOFF')
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
            'sms with pin' => ['Проверьте реквизиты перевода: карта списания **** 1234, карта зачисления **** 4321, сумма 5000,00 RUB. Пароль для подтверждения - 10001. Никому не сообщайте пароль.'],
            'transfer without description' => ['VISA1111 22:50 перевод 5000р Баланс: 14174.22р'],
            'confirm sms for transfer' => ['Для перевода 4000р получателю SOME PERSON X. на карту VISA4444 с карты VISA7777 отправьте код 49762 на номер 900. Комиссия не взимается. Добавьте сообщение получателю, набрав его после кода. Например, 49762 сообщение получателю.'],
            'transfer request' => ['Перевод 9000000001 2579'],
            'transfer error' => ['Операция не выполнена. Превышена допустимая сумма перевода для получателя. Повторите операцию через 20 часов 32 минут.'],
            'payment sms' => ['VISA1111 21:56 Оплата 610.10р Баланс: 21237.54р'],
            'purchase sms' => ['VISA8413 20:46 Покупка 30р ENERGY POINT Баланс: 2261.20р'],
            'refill sms' => ['VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р'],
            'withdrawal sms' => ['VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'],
        ];
    }
}