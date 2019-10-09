<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\CanNotCreateBillException;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\BillsFactory;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\ConfirmationMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\ConfirmationMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\PinMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\PinMessageStep;

final class BillsFactoryTest extends TestCase
{
    public function testCreateBill(): void
    {
        $pinReceivingDate = '2019-10-08 15:22:00';
        $confirmationReceivingDate = '2019-10-08 15:25:00';
        $confirmationDate = '2019-10-08 15:23:00';

        $pinStep = new PinMessageStep(
            new Message('XXX', new DateTimeImmutable($pinReceivingDate), 'PIN'),
            PinMatches::fromPregMatches([
                'account' => 'SENDER',
                'amount' => 123.45,
                'currency' => 'EUR',
                'description' => 'PIN DESCRIPTION',
            ])
        );

        $confirmationStep = new ConfirmationMessageStep(
            new Message('XXX', new DateTimeImmutable($confirmationReceivingDate), 'CONFIRM'),
            new ConfirmationMatches('SENDER', 123.45, new DateTimeImmutable($confirmationDate))
        );

        $expectedBill = new Bill(
            BillType::expense(),
            new Amount(123.45, 'EUR'),
            'SENDER',
            new BillInfo(new DateTimeImmutable($confirmationDate), 'PIN DESCRIPTION')
        );

        $this->assertEquals($expectedBill, (new BillsFactory())->createBill($pinStep, $confirmationStep));
    }

    public function testCreateBillFromPinOnly(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);
        $matches = new PinMatches('sender', 123);

        $this->expectException(CanNotCreateBillException::class);
        (new BillsFactory())->createBill(new PinMessageStep($sms, $matches));
    }

    public function testCreateBillFromConfirmationOnly(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);
        $matches = new ConfirmationMatches('sender', 123, new DateTimeImmutable());

        $this->expectException(CanNotCreateBillException::class);
        (new BillsFactory())->createBill(new ConfirmationMessageStep($sms, $matches));
    }

    public function testCreateBillWithWrongSteps(): void
    {
        $this->expectException(CanNotCreateBillException::class);
        (new BillsFactory())->createBill($this->createMock(OperationStepInterface::class));
    }
}