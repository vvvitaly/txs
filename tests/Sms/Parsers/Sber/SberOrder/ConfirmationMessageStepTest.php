<?php

/** @noinspection PhpMissingDocCommentInspection */

namespace tests\Sms\Parsers\Sber\SberOrder;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\ConfirmationMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\ConfirmationMessageStep;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\PinMatches;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\PinMessageStep;

class ConfirmationMessageStepTest extends TestCase
{
    private const CONFIRMATION_SMS_DATE = '2019-10-08 10:51:45';

    public function providerIsRelatedToStep(): array
    {
        $smsDate = new DateTimeImmutable(self::CONFIRMATION_SMS_DATE);

        return [
            'other confirmation' => [
                new ConfirmationMessageStep(
                    new Message('XXX', clone $smsDate, 'test'),
                    new ConfirmationMatches('#1', 'STORE', 123.45, 'USD')
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
                    new PinMatches('#1', 'STORE', 'VISA0001')
                ),
                true,
            ],
            'pin, wrong date' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    new PinMatches('#1', 'STORE', 'VISA0001')
                ),
                false,
            ],
            'pin, wrong order' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    new PinMatches('#2', 'STORE', 'VISA0001')
                ),
                false,
            ],
            'pin, wrong store' => [
                new PinMessageStep(
                    new Message('XXX', $smsDate->modify('+1 second'), 'test'),
                    new PinMatches('#1', 'OTHER STORE', 'VISA0001')
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
        $matches = new ConfirmationMatches('#1', 'STORE', 123.45, 'USD');

        $step = new ConfirmationMessageStep($sms, $matches);

        $this->assertSame($expectedRelated, $step->isRelatedToStep($testingStep));
    }
}
