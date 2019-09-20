<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use DateTimeImmutable;
use vvvitaly\txs\Sms\Message;

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
     * If SMS was sent on the same day when the correspondent transaction was performed, then the "time" format is
     * used (without date). Otherwise the message contains date with time or date only.
     * If parsed date has only time, this method uses the date from SMS metadata and time from the text. If SMS contains
     * both date and time, then whole parsed date will be used.
     *
     * @param Message $sms
     * @param string $parsedDate text with date
     *
     * @return DateTimeImmutable
     */
    private function resolveDate(Message $sms, string $parsedDate): DateTimeImmutable
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