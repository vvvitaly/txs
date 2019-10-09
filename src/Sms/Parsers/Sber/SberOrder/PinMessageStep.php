<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;

/**
 * PIN request.
 */
final class PinMessageStep implements OperationStepInterface
{
    /**
     * @var Message
     */
    private $originSms;

    /**
     * @var PinMatches
     */
    private $parsedMatches;

    /**
     * @var int
     */
    private $pinLifetime;

    /**
     * @param Message $originSms
     * @param PinMatches $parsedMatches
     * @param int $pinLifetime Maximum time in seconds between receiving PIN message and transfer message
     */
    public function __construct(
        Message $originSms,
        PinMatches $parsedMatches,
        int $pinLifetime = 600
    ) {
        $this->originSms = $originSms;
        $this->parsedMatches = $parsedMatches;
        $this->pinLifetime = $pinLifetime;
    }

    /**
     * @inheritDoc
     */
    public function isTerminalStep(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isRelatedToStep(OperationStepInterface $operationStep): bool
    {
        if (!$operationStep instanceof ConfirmationMessageStep) {
            return false;
        }

        /** @var ConfirmationMessageStep $operationStep */
        return $operationStep->isRelatedToStep($this);
    }

    /**
     * @return Message
     */
    public function getOriginSms(): Message
    {
        return $this->originSms;
    }

    /**
     * @return PinMatches
     */
    public function getParsedMatches(): PinMatches
    {
        return $this->parsedMatches;
    }

    /**
     * @return int
     */
    public function getPinLifetime(): int
    {
        return $this->pinLifetime;
    }
}