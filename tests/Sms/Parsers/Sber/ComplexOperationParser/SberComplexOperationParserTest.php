<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\PinParser;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\BillsFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\CanNotCreateBillException;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\SberComplexOperationParser;

final class SberComplexOperationParserTest extends TestCase
{
    public function testParseUnknownOperation(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);

        $stepParser = $this->createMock(OperationStepParserInterface::class);
        $stepParser->expects($this->once())
            ->method('parseStep')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $billsFactory = $this->createMock(BillsFactoryInterface::class);
        $billsFactory->expects($this->never())
            ->method('createBill');

        $parser = new SberComplexOperationParser($stepParser, $billsFactory);

        $this->assertNull($parser->parse($sms));
    }

    public function testParseOperationStepWithIncompleteInformation(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);
        $incompleteStep = $this->createMock(OperationStepInterface::class);
        $incompleteStep->expects($this->once())
            ->method('isTerminalStep')
            ->willReturn(false);

        $stepParser = $this->createMock(OperationStepParserInterface::class);
        $stepParser->expects($this->once())
            ->method('parseStep')
            ->with($this->identicalTo($sms))
            ->willReturn($incompleteStep);

        $billsFactory = $this->createMock(BillsFactoryInterface::class);
        $billsFactory->expects($this->never())
            ->method('createBill');

        $parser = new SberComplexOperationParser($stepParser, $billsFactory);

        $this->assertNull($parser->parse($sms));
    }

    public function testParseTerminalMessage(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);
        $bill = new Bill(BillType::expense(), new Amount(1));

        $terminalStep = $this->createMock(OperationStepInterface::class);
        $terminalStep->expects($this->once())
            ->method('isTerminalStep')
            ->willReturn(true);

        $stepParser = $this->createMock(OperationStepParserInterface::class);
        $stepParser->expects($this->once())
            ->method('parseStep')
            ->with($this->identicalTo($sms))
            ->willReturn($terminalStep);

        $billsFactory = $this->createMock(BillsFactoryInterface::class);
        $billsFactory->expects($this->once())
            ->method('createBill')
            ->with($this->identicalTo($terminalStep))
            ->willReturn($bill);

        $parser = new SberComplexOperationParser($stepParser, $billsFactory);

        $this->assertSame($bill, $parser->parse($sms));
    }

    public function testParseWhenCanNotCreateBill(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), __FUNCTION__);

        $terminalStep = $this->createMock(OperationStepInterface::class);
        $terminalStep->expects($this->once())
            ->method('isTerminalStep')
            ->willReturn(true);

        $stepParser = $this->createMock(OperationStepParserInterface::class);
        $stepParser->expects($this->once())
            ->method('parseStep')
            ->with($this->identicalTo($sms))
            ->willReturn($terminalStep);

        $billsFactory = $this->createMock(BillsFactoryInterface::class);
        $billsFactory->expects($this->once())
            ->method('createBill')
            ->with($this->identicalTo($terminalStep))
            ->willThrowException(new CanNotCreateBillException('test'));

        $parser = new SberComplexOperationParser($stepParser, $billsFactory);

        $this->assertNull($parser->parse($sms));
    }

    public function testParseMultipleSteps(): void
    {
        $smses = [
            new Message('XXX', new DateTimeImmutable(), __FUNCTION__),
            new Message('XXX', new DateTimeImmutable(), __FUNCTION__),
            new Message('XXX', new DateTimeImmutable(), __FUNCTION__),
        ];

        $bill = new Bill(BillType::expense(), new Amount(1));

        /**
         * @var MockObject[] $steps
         *
         * Let the first and the last steps relate to the one operation. And the second step is separate.
         */
        $steps = [
            $this->createMock(OperationStepInterface::class),
            $this->createMock(OperationStepInterface::class),
            $this->createMock(OperationStepInterface::class),
        ];

        $steps[0]->expects($this->never())
            ->method('isRelatedToStep');
        $steps[0]->expects($this->once())
            ->method('isTerminalStep')
            ->willReturn(false);

        $steps[1]->expects($this->once())
            ->method('isRelatedToStep')
            ->with($this->identicalTo($steps[0]))
            ->willReturn(false);
        $steps[1]->expects($this->once())
            ->method('isTerminalStep')
            ->willReturn(false);

        $steps[2]->expects($this->exactly(2))
            ->method('isRelatedToStep')
            ->withConsecutive(
                $this->identicalTo($steps[0]),
                $this->identicalTo($steps[1])
            )
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );
        $steps[2]->expects($this->once())
            ->method('isTerminalStep')
            ->willReturn(true);

        $stepParser = $this->createMock(OperationStepParserInterface::class);
        $stepParser->expects($this->exactly(3))
            ->method('parseStep')
            ->withConsecutive(
                $this->identicalTo($smses[0]),
                $this->identicalTo($smses[1]),
                $this->identicalTo($smses[2])
            )
            ->willReturnOnConsecutiveCalls(...$steps);

        $billsFactory = $this->createMock(BillsFactoryInterface::class);
        $billsFactory->expects($this->once())
            ->method('createBill')
            ->with(
                $steps[0],
                $steps[2]
            )
            ->willReturn($bill);

        $parser = new SberComplexOperationParser($stepParser, $billsFactory);

        $actualBills = [];
        foreach ($smses as $sms) {
            $actualBills[] = $parser->parse($sms);
        }

        $this->assertNull($actualBills[0]);
        $this->assertNull($actualBills[1]);
        $this->assertSame($bill, $actualBills[2]);
    }
}