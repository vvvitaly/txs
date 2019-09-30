<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\PinParser;

use ArrayObject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ArrayStorage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;

final class ArrayStorageTest extends TestCase
{
    public function testSavePinMessage(): void
    {
        $mem = new ArrayObject();
        $pin1 = new PinMessage();
        $pin2 = new PinMessage();

        $storage = new ArrayStorage($mem, static function () {
            return true;
        });
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
        $matcher = static function (PinMessage $pinMessage): bool {
            return $pinMessage->account === 'VISA1001';
        };

        $storage = new ArrayStorage($mem, $matcher);

        $pins = [];
        for ($i = 0; $i < 3; $i++) {
            $pin = new PinMessage();
            $pin->receivingDate = new DateTimeImmutable();
            $pin->account = 'VISA100' . $i;
            $pin->amount = 100.45 + $i;
            $pin->currency = 'р';
            $pin->description = 'test-' . $i;

            $storage->savePinMessage($pin);
            $pins[$i] = $pin;
        }

        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->operationDate = new DateTimeImmutable();
        $transfer->amount = 101.45;
        $transfer->account = 'VISA1001';

        $actualPin = $storage->findPinMessage($transfer);

        $this->assertSame($pins[1], $actualPin);
    }
}