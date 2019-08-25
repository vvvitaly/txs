<?php

declare(strict_types=1);

namespace App\Sms\Parsers;

use App\Core\Bills\Bill;
use App\Sms\Message;

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