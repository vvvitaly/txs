<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\BillsFactory;

final class BillsFactoryTest extends TestCase
{
    public function testParseCreateBill(): void
    {
        $pin = new PinMessage();
        $pin->receivingDate = new DateTimeImmutable('2019-09-30 09:01:00');
        $pin->description = 'Перевод SOMEBODY на VISA0002 с карты VISA0001';
        $pin->account = 'test';
        $pin->amount = 123.45;
        $pin->currency = 'RUB';

        $confirm = new ConfirmationMessage();
        $confirm->receivingDate = new DateTimeImmutable('2019-09-30 09:05:00');
        $confirm->operationDate = new DateTimeImmutable('2019-09-30 09:03:00');
        $confirm->account = 'test';
        $confirm->amount = 123.45;

        $expectedBill = new Bill(
            BillType::expense(),
            new Amount(123.45, 'RUB'),
            'test',
            new BillInfo(new DateTimeImmutable('2019-09-30 09:03:00'), 'Перевод SOMEBODY на VISA0002 с карты VISA0001')
        );

        $this->assertEquals($expectedBill, (new BillsFactory())->createBill($pin, $confirm));
    }
}