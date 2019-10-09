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
                    PinMatches::fromPregMatches(['account' => '1234', 'amount' => 123.45])
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
                    new ConfirmationMatches('1234', 123.45, $pinSmsDate->modify('+1 minute'))
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
        $matches = PinMatches::fromPregMatches([
            'account' => '1234',
            'amount' => 123.45,
            'currency' => 'RUB',
            'description' => __FUNCTION__,
        ]);

        $step = new PinMessageStep($sms, $matches);

        $this->assertSame($expectedRelated, $step->isRelatedToStep($testingStep));
    }
}
