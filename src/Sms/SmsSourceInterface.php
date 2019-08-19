<?php

declare(strict_types=1);

namespace App\Sms;

use DateTimeImmutable;
use Generator;

/**
 * SMS provider
 */
interface SmsSourceInterface
{
    /**
     * Read the next SMS from source. Should return `Sms` instance. Each SMS has to be in dates interval [dateBegin, dateEnd].
     *
     * @param DateTimeImmutable $dateBegin
     * @param DateTimeImmutable $dateEnd
     *
     * @return Generator|Sms
     */
    public function read(DateTimeImmutable $dateBegin, DateTimeImmutable $dateEnd): Generator;
}