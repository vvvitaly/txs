<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\PinParser;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\BillsFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinParserFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\SberPinParser;

final class SberPinParserTest extends TestCase
{
    private function mockParser(
        MessagesStorageInterface $storage,
        PinSmsParserInterface $pinSmsParser,
        ConfirmationSmsParserInterface $confirmationSmsParser,
        BillsFactoryInterface $billsFactory
    ): SberPinParser {
        $factory = $this->createConfiguredMock(PinParserFactoryInterface::class, [
            'getMessagesStorage' => $storage,
            'getPinSmsParser' => $pinSmsParser,
            'getConfirmationSmsParser' => $confirmationSmsParser,
            'getBillsFactory' => $billsFactory,
        ]);

        return new SberPinParser($factory);
    }

    public function actionParseNotValidSms(): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(), 'some text');

        $storage = $this->createMock(MessagesStorageInterface::class);
        $pinSmsParser = $this->createMock(PinSmsParserInterface::class);
        $confirmationSmsParser = $this->createMock(ConfirmationSmsParserInterface::class);
        $billsFactory = $this->createMock(BillsFactoryInterface::class);

        $pinSmsParser->expects($this->never())
            ->method('parseSms');
        $confirmationSmsParser->expects($this->never())
            ->method('parseSms');

        $parser = $this->mockParser($storage, $pinSmsParser, $confirmationSmsParser, $billsFactory);

        $this->assertNull($parser->parse($sms));
    }

    public function testParsePinMessage(): void
    {
        $sms = new Message('900', new DateTimeImmutable(), 'some text');
        $pin = new PinMessage();

        $storage = $this->createMock(MessagesStorageInterface::class);
        $pinSmsParser = $this->createMock(PinSmsParserInterface::class);
        $confirmationSmsParser = $this->createMock(ConfirmationSmsParserInterface::class);
        $billsFactory = $this->createMock(BillsFactoryInterface::class);

        $pinSmsParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn($pin);

        $confirmationSmsParser->expects($this->never())
            ->method('parseSms');

        $storage->expects($this->once())
            ->method('savePinMessage')
            ->with($this->identicalTo($pin));

        $storage->expects($this->never())
            ->method('findPinMessage');

        $parser = $this->mockParser($storage, $pinSmsParser, $confirmationSmsParser, $billsFactory);

        $this->assertNull($parser->parse($sms));
    }

    public function testParseConfirmationMessage(): void
    {
        $sms = new Message('900', new DateTimeImmutable(), 'some text');
        $pin = new PinMessage();
        $confirmation = new ConfirmationMessage();
        $expectedBill = new Bill(
            BillType::expense(),
            new Amount(1),
            'test'
        );

        $storage = $this->createMock(MessagesStorageInterface::class);
        $pinSmsParser = $this->createMock(PinSmsParserInterface::class);
        $confirmationSmsParser = $this->createMock(ConfirmationSmsParserInterface::class);
        $billsFactory = $this->createMock(BillsFactoryInterface::class);

        $pinSmsParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $confirmationSmsParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn($confirmation);

        $storage->expects($this->never())
            ->method('savePinMessage');

        $storage->expects($this->once())
            ->method('findPinMessage')
            ->with($this->identicalTo($confirmation))
            ->willReturn($pin);

        $billsFactory->expects($this->once())
            ->method('createBill')
            ->with($this->identicalTo($pin), $this->identicalTo($confirmation))
            ->willReturn($expectedBill);

        $parser = $this->mockParser($storage, $pinSmsParser, $confirmationSmsParser, $billsFactory);

        $actual = $parser->parse($sms);
        $this->assertSame($expectedBill, $actual);
    }

    public function testParseWhenStorageDoesNotContainsPinMessage(): void
    {
        $sms = new Message('900', new DateTimeImmutable(), 'test');

        $storage = $this->createMock(MessagesStorageInterface::class);
        $pinSmsParser = $this->createMock(PinSmsParserInterface::class);
        $confirmationSmsParser = $this->createMock(ConfirmationSmsParserInterface::class);
        $billsFactory = $this->createMock(BillsFactoryInterface::class);

        $pinSmsParser->expects($this->once())
            ->method('parseSms')
            ->willReturn(null);

        $confirmationSmsParser->expects($this->once())
            ->method('parseSms')
            ->willReturn(new ConfirmationMessage());

        $storage->expects($this->never())
            ->method('savePinMessage');

        $storage->expects($this->once())
            ->method('findPinMessage')
            ->willReturn(null);

        $billsFactory->expects($this->never())
            ->method('createBill');

        $parser = $this->mockParser($storage, $pinSmsParser, $confirmationSmsParser, $billsFactory);

        $this->assertNull($parser->parse($sms));
    }
}