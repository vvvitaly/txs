<?php

declare(strict_types=1);

namespace App\Sms\Parsers;

use App\Sms\Sms;

/**
 * Check if SMS is from Sberbank
 */
trait SberValidationTrait
{
    /**
     * Check if the given SMS message is sent by Sberbank
     *
     * @param Sms $sms
     *
     * @return bool
     */
    private function isValid(Sms $sms): bool
    {
        return $sms->from === '900';
    }
}