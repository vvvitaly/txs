<?php

declare(strict_types=1);

namespace App\Sms;

use App\Core\Bills\Bill;

/**
 * Parse provided SMS
 */
interface MessageParserInterface
{
    /**
     * Parse the given sms. Returns NULL if there no bill in the given SMS.
     *
     * @param Sms $sms
     *
     * @return Bill
     */
    public function parse(Sms $sms): ?Bill;
}