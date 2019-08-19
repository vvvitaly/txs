<?php

declare(strict_types=1);

namespace App\Sms\Parsers;

use App\Sms\Sms;
use DateTimeImmutable;

/**
 * Resolve the correct date based on Sberbank report SMS.
 */
trait SberDatesTrait
{
    /**
     * Resolve date from the SMS.
     * Dates in SMS might have following formats:
     * - HH:MM
     * - DD.MM.YY
     * - DD.MM.YY HH:MM
     *
     * If SMS was sent on the same day when the correspondence transaction was performed, then the "time" format is used.
     * Otherwise it uses the "date" format.
     *
     * This method uses the date from SMS metadata and correct it with the time from SMS (in case of "time format"),
     * or, in case of "date" format, it uses the full date from the text.
     *
     * @param Sms $sms
     * @param string $parsedDate text with date
     *
     * @return DateTimeImmutable
     */
    private function resolveDate(Sms $sms, string $parsedDate): DateTimeImmutable
    {
        if (($date = DateTimeImmutable::createFromFormat('d.m.y H:i', $parsedDate)) !== false) {
            return $date;
        }

        if (($date = DateTimeImmutable::createFromFormat('d.m.y', $parsedDate)) !== false) {
            return $date->setTime(0, 0, 0);
        }

        if (DateTimeImmutable::createFromFormat('H:i', $parsedDate)) {
            return $sms->date->modify($parsedDate);
        }

        return $sms->date;
    }
}