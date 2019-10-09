<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberOrder;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\CanNotCreateBillException;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\BillsFactory;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\ConfirmationMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\ConfirmationMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\PinMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\PinMessageStep;

final class BillsFactoryTest extends TestCase
{
    public function testCreateBill(): void
    {
        $pinReceivingDate = '2019-10-08 15:22:00';
        $confirmationReceivingDate = '2019-10-08 15:25:00';

        $pinStep = new PinMessageStep(
            new Message('XXX', new DateTimeImmutable($pinReceivingDate), 'PIN'),
            new PinMatches('ORDER 1', 'STORE NAME', 'VISA0001')
        );

        $confirmationStep = new ConfirmationMessageStep(
            new Message('XXX', new DateTimeImmutable($confirmationReceivingDate), 'CONFIRM'),
            new ConfirmationMatches('ORDER 1', 'STORE NAME', 123.45, 'руб')
        );

        $expectedBill = new Bill(
            BillType::expense(),
            new Amount(123.45, 'руб'),
            'VISA0001',
            new BillInfo(new DateTimeImmutable($confirmationReceivingDate), 'Заказ в магазине STORE NAME')
        );

        $this->assertEquals($expectedBill, (new BillsFactory())->createBill($pinStep, $confirmationStep));
    }

    public function testCreateBillFromPinOnly(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);
        $matches = new PinMatches('ORDER 1', 'STORE', 'XXX');

        $this->expectException(CanNotCreateBillException::class);
        (new BillsFactory())->createBill(new PinMessageStep($sms, $matches));
    }

    public function testCreateBillFromConfirmationOnly(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);
        $matches = new ConfirmationMatches('ORDER 1', 'STORE', 123.45, 'RUB');

        $this->expectException(CanNotCreateBillException::class);
        (new BillsFactory())->createBill(new ConfirmationMessageStep($sms, $matches));
    }

    public function testCreateBillWithWrongSteps(): void
    {
        $this->expectException(CanNotCreateBillException::class);
        (new BillsFactory())->createBill($this->createMock(OperationStepInterface::class));
    }
}