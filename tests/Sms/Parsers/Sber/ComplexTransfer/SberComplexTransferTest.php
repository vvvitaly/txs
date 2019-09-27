<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\ComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillType;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\MessagesStorageInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\PinSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\SberComplexTransfer;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferMessage;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferPinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferSmsParserInterface;

final class SberComplexTransferTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function providerParseWrongBody(): array
    {
        return [
            'SberPayment' => ['ECMC1234 02:38 Оплата 100р TELE2 Баланс: 14074.22р'],
            'SberRefill' => ['VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р'],
            'SberTransfer' => ['С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'],
            'SberWithdrawal' => ['VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р'],
            'SberPurchase' => ['VISA1111 20:46 Покупка 1230.22р XXXX YYY Баланс: 2261.20р'],
            'SberRefund' => ['VISA1234 16.07.19 возврат покупки 111.09р XXXXX Баланс: 14867.80р'],
            'transfer request' => ['Перевод 9000000001 2579'],
            'transfer error' => ['Операция не выполнена. Превышена допустимая сумма перевода для получателя. Повторите операцию через 20 часов 32 минут.'],
            'transfer card -> account' => ['С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.'],
            'transfer account -> card' => ['С Вашего счета 11111111111111111857 произведен перевод на карту № **** 4321 на сумму 19000,00 RUB.'],
            'transfer card -> card' => ['С Вашей карты **** 7777 произведен перевод на карту № **** 0001 на сумму 6154,00 RUB.'],
        ];
    }

    /**
     * @dataProvider providerParseWrongBody
     *
     * @param string $messageText
     */
    public function testParserWrongBody(string $messageText): void
    {
        $sms = new Message('900', new DateTimeImmutable(), $messageText);

        $pinParser = $this->createMock(PinSmsParserInterface::class);
        $pinParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $transferParser = $this->createMock(TransferSmsParserInterface::class);
        $transferParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $storage = $this->createMock(MessagesStorageInterface::class);
        $storage->expects($this->never())
            ->method('findPinMessage');

        $parser = new SberComplexTransfer($storage, $pinParser, $transferParser);
        $bill = $parser->parse($sms);

        $this->assertNull($bill);
    }

    public function testParsePinMessage(): void
    {
        $sms = new Message(
            '900',
            new DateTimeImmutable(),
            'some text'
        );

        $pin = new TransferPinMessage();

        $pinParser = $this->createMock(PinSmsParserInterface::class);
        $pinParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn($pin);

        $transferParser = $this->createMock(TransferSmsParserInterface::class);
        $transferParser->expects($this->never())
            ->method('parseSms');

        $storage = $this->createMock(MessagesStorageInterface::class);
        $storage->expects($this->once())
            ->method('savePinMessage')
            ->with($this->identicalTo($pin));

        $storage->expects($this->never())
            ->method('findPinMessage');

        $parser = new SberComplexTransfer($storage, $pinParser, $transferParser);
        $bill = $parser->parse($sms);

        $this->assertNull($bill);
    }

    public function testParseRegularTransferMessage(): void
    {
        $receivingDate = new DateTimeImmutable('2019-09-25');
        $sms = new Message(
            '900',
            $receivingDate,
            'some text'
        );

        $pin = new TransferPinMessage();
        $pin->amount = 1.;
        $pin->currency = 'RUB';
        $pin->account = 'testPin';
        $pin->description = 'test';

        $transfer = new TransferMessage();
        $transfer->receivingDate = $receivingDate;
        $transfer->transferDate = new DateTimeImmutable('2019-09-26');
        $transfer->account = 'testTr';
        $transfer->amount = 2.;

        $expectedBill = new Bill(
            BillType::expense(),
            new Amount($pin->amount, $pin->currency),
            $transfer->account,
            new BillInfo($transfer->transferDate, $pin->description)
        );

        $pinParser = $this->createMock(PinSmsParserInterface::class);
        $pinParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $transferParser = $this->createMock(TransferSmsParserInterface::class);
        $transferParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn($transfer);

        $storage = $this->createMock(MessagesStorageInterface::class);
        $storage->expects($this->never())
            ->method('savePinMessage');

        $storage->expects($this->once())
            ->method('findPinMessage')
            ->with($this->identicalTo($transfer))
            ->willReturn($pin);

        $parser = new SberComplexTransfer($storage, $pinParser, $transferParser);
        $bill = $parser->parse($sms);

        $this->assertEquals($expectedBill, $bill);
    }

    public function testParseWhenStorageDoesNotContainsPinMessage(): void
    {
        $sms = new Message('900', new DateTimeImmutable(), 'VISA0001 31.07.19 перевод 455р Баланс: 7673.22р');
        $transfer = new TransferMessage();

        $pinParser = $this->createMock(PinSmsParserInterface::class);
        $pinParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn(null);

        $transferParser = $this->createMock(TransferSmsParserInterface::class);
        $transferParser->expects($this->once())
            ->method('parseSms')
            ->with($this->identicalTo($sms))
            ->willReturn($transfer);

        $storage = $this->createMock(MessagesStorageInterface::class);
        $storage->expects($this->once())
            ->method('findPinMessage')
            ->with($this->identicalTo($transfer))
            ->willReturn(null);

        $parser = new SberComplexTransfer($storage, $pinParser, $transferParser);
        $bill = $parser->parse($sms);

        $this->assertNull($bill);
    }
}