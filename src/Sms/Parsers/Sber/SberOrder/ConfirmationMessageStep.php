<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;

/**
 * Order confirmation
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

        if ($this->parsedMatches->orderId !== $operationStep->getParsedMatches()->orderId) {
            return false;
        }

        if ($this->parsedMatches->store !== $operationStep->getParsedMatches()->store) {
            return false;
        }

        $pinTime = $operationStep->getOriginSms()->date->getTimestamp();
        $confirmationTime = $this->originSms->date->getTimestamp();

        return $confirmationTime >= $pinTime;
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