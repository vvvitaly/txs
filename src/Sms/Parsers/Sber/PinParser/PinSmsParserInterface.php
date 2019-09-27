<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use vvvitaly\txs\Sms\Message;

/**
 * Parse PIN messages
 */
interface PinSmsParserInterface
{
    /**
     * Parse the message. If message is NOT PIN SMS, returns NULL.
     *
     * @param \vvvitaly\txs\Sms\Message $message
     *
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage|null
     */
    public function parseSms(Message $message): ?PinMessage;
}