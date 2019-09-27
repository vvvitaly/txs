<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\ComplexTransfer;

use ArrayObject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\ArrayStorage;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferMessage;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferPinMessage;

final class ArrayStorageTest extends TestCase
{
    public function testSavePinMessage(): void
    {
        $mem = new ArrayObject();
        $pin1 = new TransferPinMessage();
        $pin2 = new TransferPinMessage();

        $storage = new ArrayStorage($mem);
        $storage->savePinMessage($pin1);
        $storage->savePinMessage($pin2);

        $this->assertEquals(2, $mem->count());

        $saved = array_values($mem->getArrayCopy());
        $this->assertSame($pin1, $saved[0]);
        $this->assertSame($pin2, $saved[1]);
    }

    public function testFindPinMessage(): void
    {
        $mem = new ArrayObject();
        $storage = new ArrayStorage($mem);

        $pins = [];
        for ($i = 0; $i < 3; $i++) {
            $pin = new TransferPinMessage();
            $pin->receivingDate = new DateTimeImmutable();
            $pin->account = 'VISA100' . $i;
            $pin->amount = 100.45 + $i;
            $pin->currency = 'р';
            $pin->description = 'test-' . $i;

            $storage->savePinMessage($pin);
            $pins[$i] = $pin;
        }

        $transfer = new TransferMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->transferDate = new DateTimeImmutable();
        $transfer->amount = 101.45;
        $transfer->account = 'VISA1001';

        $actualPin = $storage->findPinMessage($transfer);

        $this->assertSame($pins[1], $actualPin);
    }

    public function testFindPinMessageByPartialAccount(): void
    {
        $mem = new ArrayObject();
        $storage = new ArrayStorage($mem);

        $pin = new TransferPinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = '1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $storage->savePinMessage($pin);

        $transfer = new TransferMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->transferDate = new DateTimeImmutable();
        $transfer->amount = 123.45;
        $transfer->account = 'VISA1000';

        $this->assertSame($pin, $storage->findPinMessage($transfer));
    }

    public function testFindPinMessageShouldSearchByAccount(): void
    {
        $mem = new ArrayObject();
        $storage = new ArrayStorage($mem);

        $pin = new TransferPinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $storage->savePinMessage($pin);

        $transfer = new TransferMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->transferDate = new DateTimeImmutable();
        $transfer->amount = 123.45;
        $transfer->account = 'some-other-account';

        $this->assertNull($storage->findPinMessage($transfer));
    }

    public function testFindPinMessageShouldSearchByAmount(): void
    {
        $mem = new ArrayObject();
        $storage = new ArrayStorage($mem);

        $pin = new TransferPinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $storage->savePinMessage($pin);

        $transfer = new TransferMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->transferDate = new DateTimeImmutable();
        $transfer->amount = 999999;
        $transfer->account = 'VISA1000';

        $this->assertNull($storage->findPinMessage($transfer));
    }

    public function testFindPinMessageShouldSearchByReceivingDate(): void
    {
        $mem = new ArrayObject();
        $storage = new ArrayStorage($mem, 600);

        $date = new DateTimeImmutable();

        $pin = new TransferPinMessage();
        $pin->receivingDate = $date->modify('-1 year');
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $storage->savePinMessage($pin);

        $transfer = new TransferMessage();
        $transfer->receivingDate = $date;
        $transfer->transferDate = new DateTimeImmutable();
        $transfer->amount = 123.45;
        $transfer->account = 'VISA1000';

        $this->assertNull($storage->findPinMessage($transfer));
    }
}