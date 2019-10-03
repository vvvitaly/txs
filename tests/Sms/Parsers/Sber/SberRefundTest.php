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
use vvvitaly\txs\Sms\Parsers\Sber\SberRefund;

final class SberRefundTest extends SberSmsTestCase
{
    /**
     * @inheritDoc
     */
    protected function createParser(): MessageParserInterface
    {
        return new SberRefund();
    }

    /**
     * @inheritDoc
     */
    public function providerParseRegularMessage(): array
    {
        return [
            'regular refund' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA1234 19:23 возврат покупки 111.09р XXXXX Баланс: 14867.80р'),
                new Bill(
                    BillType::income(),
                    new Amount(111.09, 'р'),
                    'VISA1234',
                    new BillInfo(new DateTimeImmutable('2019-08-01 19:23:00'), 'Возврат от XXXXX')
                ),
            ],
            'refund, full date' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA9009 31.07.19 20:38 возврат покупки 256.51р XXXXX YY Баланс: 11905.22р'),
                new Bill(
                    BillType::income(),
                    new Amount(256.51, 'р'),
                    'VISA9009',
                    new BillInfo(new DateTimeImmutable('2019-07-31 20:38:00'), 'Возврат от XXXXX YY')
                ),
            ],
            'refund, date only' => [
                new Message('900', new DateTimeImmutable('2019-08-01 23:01:13'),
                    'VISA9009 31.07.19 возврат покупки 256.51р XXXXX YY Баланс: 11905.22р'),
                new Bill(
                    BillType::income(),
                    new Amount(256.51, 'р'),
                    'VISA9009',
                    new BillInfo(new DateTimeImmutable('2019-07-31 00:00:00'), 'Возврат от XXXXX YY')
                ),
            ],
        ];
    }
}