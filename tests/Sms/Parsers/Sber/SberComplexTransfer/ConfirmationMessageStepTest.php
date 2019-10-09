<?php

/** @noinspection PhpMissingDocCommentInspection */

namespace tests\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\ConfirmationMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\ConfirmationMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\PinMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\PinMessageStep;

class ConfirmationMessageStepTest extends TestCase
{
    private const CONFIRMATION_SMS_DATE = '2019-10-08 10:51:45';
    private const TRANSFER_DATE = '2019-10-08 10:55:22';

    public function providerIsRelatedToStep(): array
    {
        $smsDate = new DateTimeImmutable(self::CONFIRMATION_SMS_DATE);

        return [
            'other confirmation' => [
                new ConfirmationMessageStep(
                    new Message('XXX', clone $smsDate, 'test'),
                    ConfirmationMatches::fromPregMatches([
                        'account' => 'VISA1234',
                        'amount' => 123.45,
                        'time' => new DateTimeImmutable(self::TRANSFER_DATE),
                    ])
                ),
                false,
            ],
            'unknown type' => [
                $this->createMock(OperationStepInterface::class),
                false,
            ],
            'pin' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('-1 minute'), 'test'),
                    PinMatches::fromPregMatches(['account' => 'VISA1234', 'amount' => 123.45])
                ),
                true,
            ],
            'pin, partial sender' => [
                new PinMessageStep(
                    new Message('XXX', clone $smsDate->modify('-1 minute'), 'test'),
                    PinMatches::fromPregMatches(['account' => '1234', 'amount' => 123.45])
                ),
                true,
            ],
            'expired pin' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('-1 hour'), 'test'),
                    PinMatches::fromPregMatches(['account' => 'VISA1234', 'amount' => 123.45]),
                    $ttl = 59 * 60
                ),
                false,
            ],
            'pin, wrong date' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    PinMatches::fromPregMatches(['account' => 'VISA1234', 'amount' => 123.45])
                ),
                false,
            ],
            'pin, wrong sender' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    PinMatches::fromPregMatches(['account' => 'VISA1111', 'amount' => 123.45])
                ),
                false,
            ],
            'pin, wrong partial sender' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    PinMatches::fromPregMatches(['account' => '1111', 'amount' => 123.45])
                ),
                false,
            ],
            'pin, wrong amount' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    PinMatches::fromPregMatches(['account' => 'VISA1234', 'amount' => 321.99])
                ),
                false,
            ],
        ];
    }

    /**
     * @param OperationStepInterface $testingStep
     * @param bool $expectedRelated
     *
     * @throws Exception
     *
     * @dataProvider providerIsRelatedToStep
     */
    public function testIsRelatedToStep(OperationStepInterface $testingStep, bool $expectedRelated): void
    {
        $sms = new Message('XXX', new DateTimeImmutable(self::CONFIRMATION_SMS_DATE), __FUNCTION__);
        $matches = new ConfirmationMatches('VISA1234', 123.45, new DateTimeImmutable(self::TRANSFER_DATE));

        $step = new ConfirmationMessageStep($sms, $matches);

        $this->assertSame($expectedRelated, $step->isRelatedToStep($testingStep));
    }
}
