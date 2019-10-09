<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser;

use vvvitaly\txs\Sms\Message;

/**
 * Parse SMS with one step
 */
interface OperationStepParserInterface
{
    /**
     * Parse the message.
     *
     * @param Message $message
     *
     * @return OperationStepInterface|null
     */
    public function parseStep(Message $message): ?OperationStepInterface;
}