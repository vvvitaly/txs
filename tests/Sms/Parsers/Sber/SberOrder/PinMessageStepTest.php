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

class PinMessageStepTest extends TestCase
{
    private const PIN_SMS_DATE = '2019-10-08 10:51:45';

    public function providerIsRelatedToStep(): array
    {
        $pinSmsDate = new DateTimeImmutable(self::PIN_SMS_DATE);

        return [
            'other pin' => [
                new PinMessageStep(
                    new Message('XXX', clone $pinSmsDate, 'test'),
                    new PinMatches('#1', 'STORE', 'VISA0001')
                ),
                false,
            ],
            'unknown type' => [
                $this->createMock(OperationStepInterface::class),
                false,
            ],
            'confirmation' => [
                new ConfirmationMessageStep(
                    new Message('XXX', $pinSmsDate->modify('+1 minute'), 'test'),
                    new ConfirmationMatches('#1', 'STORE', 123.45, 'USD')
                ),
                true,
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
        $sms = new Message('XXX', new DateTimeImmutable(self::PIN_SMS_DATE), __FUNCTION__);
        $matches = new PinMatches('#1', 'STORE', 'VISA0001');

        $step = new PinMessageStep($sms, $matches);

        $this->assertSame($expectedRelated, $step->isRelatedToStep($testingStep));
    }
}
