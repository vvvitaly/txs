<?php

declare(strict_types=1);

namespace App\Sms;

use Generator;

/**
 * SMS provider
 */
interface SmsSourceInterface
{
    /**
     * Read the next SMS from source. Should return `Sms` instance
     *
     * @return Generator|Sms
     * @throws SourceReadErrorException
     */
    public function read(): Generator;
}