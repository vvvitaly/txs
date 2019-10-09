<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;

/**
 * Message with transfer confirmation in format:
 * - "{account} {time} перевод {amount}{currency symbol} Баланс: XXXXX.YY{currency}"
 *
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * For example:
 *  - "VISA0001 08:17 перевод 455р Баланс: 7673.22р"
 *
 * Confirmation message is the last in transfer operation. It can be related only to previous PIN message with the
 * same amount value and sender account name (with or without literal prefix), and received not later than specified
 * PIN lifetime value.
 */
final class ConfirmationMessageStep implements OperationStepInterface
{
    /**
     * @var Message
     */
    private $originSms;

    /**
     * @var ConfirmationMatches
     */
    private $parsedMatches;

    /**
     * @param Message $originSms
     * @param ConfirmationMatches $parsedMatches
     */
    public function __construct(
        Message $originSms,
        ConfirmationMatches $parsedMatches
    ) {
        $this->originSms = $originSms;
        $this->parsedMatches = $parsedMatches;
    }

    /**
     * @inheritDoc
     */
    public function isTerminalStep(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isRelatedToStep(OperationStepInterface $operationStep): bool
    {
        if (!$operationStep instanceof PinMessageStep) {
            return false;
        }

        /** @var PinMessageStep $operationStep */

        if (preg_match("/^[A-Z]*{$operationStep->getParsedMatches()->senderAccount}$/",
                $this->parsedMatches->account) !== 1) {
            return false;
        }

        if ($this->parsedMatches->amount !== $operationStep->getParsedMatches()->amount) {
            return false;
        }

        $pinTime = $operationStep->getOriginSms()->date->getTimestamp();
        $transferTime = $this->originSms->date->getTimestamp();

        return $transferTime >= $pinTime && ($transferTime - $pinTime <= $operationStep->getPinLifetime());
    }

    /**
     * @return Message
     */
    public function getOriginSms(): Message
    {
        return $this->originSms;
    }

    /**
     * @return ConfirmationMatches
     */
    public function getParsedMatches(): ConfirmationMatches
    {
        return $this->parsedMatches;
    }
}