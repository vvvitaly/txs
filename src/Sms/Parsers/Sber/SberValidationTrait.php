<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Sms\Message;

/**
 * Check if SMS is from Sberbank
 */
trait SberValidationTrait
{
    /**
     * Check if the given SMS message is sent by Sberbank
     *
     * @param Message $sms
     *
     * @return bool
     */
    private function isValid(Message $sms): bool
    {
        return $sms->from === '900';
    }
}