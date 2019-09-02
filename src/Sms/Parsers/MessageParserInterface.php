<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;

/**
 * Parse provided SMS
 */
interface MessageParserInterface
{
    /**
     * Parse the given sms. Returns NULL if there no bill in the given SMS.
     *
     * @param Message $sms
     *
     * @return Bill
     */
    public function parse(Message $sms): ?Bill;
}