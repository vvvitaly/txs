<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\TransferSmsPinMatcher;

final class PinSmsMatcherTest extends TestCase
{
    public function testInvoke(): void
    {
        $pin = new PinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->operationDate = new DateTimeImmutable();
        $transfer->account = 'VISA1000';
        $transfer->amount = 123.45;

        $matcher = new TransferSmsPinMatcher();

        $this->assertTrue($matcher($pin, $transfer));
    }

    public function testInvokeWhenMatchesByPartialAccount(): void
    {
        $pin = new PinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = '1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->operationDate = new DateTimeImmutable();
        $transfer->account = 'VISA1000';
        $transfer->amount = 123.45;

        $matcher = new TransferSmsPinMatcher();

        $this->assertTrue($matcher($pin, $transfer));
    }

    public function testInvokeShouldMatchAccount(): void
    {
        $pin = new PinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->operationDate = new DateTimeImmutable();
        $transfer->account = 'some-other-account';
        $transfer->amount = 123.45;

        $matcher = new TransferSmsPinMatcher();

        $this->assertFalse($matcher($pin, $transfer));
    }

    public function testInvokeShouldMatchAmount(): void
    {
        $pin = new PinMessage();
        $pin->receivingDate = new DateTimeImmutable();
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = new DateTimeImmutable();
        $transfer->operationDate = new DateTimeImmutable();
        $transfer->account = 'VISA1000';
        $transfer->amount = 999999;

        $matcher = new TransferSmsPinMatcher();

        $this->assertFalse($matcher($pin, $transfer));
    }

    public function testInvokeShouldMatchReceivingDate(): void
    {
        $date = new DateTimeImmutable();

        $pin = new PinMessage();
        $pin->receivingDate = $date->modify('-1 hour');
        $pin->account = 'VISA1000';
        $pin->amount = 123.45;
        $pin->currency = 'р';
        $pin->description = 'test';

        $transfer = new ConfirmationMessage();
        $transfer->receivingDate = $date;
        $transfer->operationDate = new DateTimeImmutable();
        $transfer->account = 'VISA1000';
        $transfer->amount = 123.45;

        $matcher = new TransferSmsPinMatcher($ttl = 59 * 60);

        $this->assertFalse($matcher($pin, $transfer));
    }
}