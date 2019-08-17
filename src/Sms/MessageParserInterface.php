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
     * Parse the given sms. Throws an `UnknownSmsTypeException` exception if parser can't process this SMS.
     *
     * @param Sms $sms
     *
     * @return Bill
     * @throws UnknownSmsTypeException
     */
    public function parse(Sms $sms): Bill;
}