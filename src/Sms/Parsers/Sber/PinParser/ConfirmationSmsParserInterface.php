<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use vvvitaly\txs\Sms\Message;

/**
 * Parse confirmation SMS
 */
interface ConfirmationSmsParserInterface
{
    /**
     * Parse the message. If SMS is NOT transfer SMS returns null.
     *
     * @param \vvvitaly\txs\Sms\Message $message
     *
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage|null
     */
    public function parseSms(Message $message): ?ConfirmationMessage;
}