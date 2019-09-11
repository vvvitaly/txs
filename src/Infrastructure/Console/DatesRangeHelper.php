<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Helper\Helper;
use vvvitaly\txs\Libs\Date\DatesRange;

/**
 * Helper for process dates range parameter
 */
final class DatesRangeHelper extends Helper
{
    /**
     * Get common help text for commands configuration
     *
     * @return string
     */
    public static function getHelp(): string
    {
        return <<<EOS
The dates range is a string contains one or two dates separated with colon. Both dates are included in the range. Any date 
can be omitted. If the range contains only one day a short form with one date and without colon might be used. E.g.:
    * <comment>2019-01-01:2019-02-01</comment> - dates from "2019-01-01" to "2019-02-01" 
    * <comment>:2019-01-01</comment> - all dates to "2019-01-01"
    * <comment>2019-01-01:</comment> - dates from "2019-01-01" to today
    * <comment>2019-01-01</comment> - only one date "2019-01-01"
EOS;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'datesRange';
    }

    /**
     * Parse dates range from the given string. The given string can contain one or two dates separated with colon.
     * Both dates are included in the range. Any date can be omitted. If the range contains only one day a short form
     * with one date and without colon might be used. E.g.:
     *  - 2019-01-01:2019-02-01 - dates from "2019-01-01" to "2019-02-01"
     *  - :2019-01-01 - all dates to "2019-01-01"
     *  - 2019-01-01: - dates from "2019-01-01" to today
     *  - 2019-01-01 - only one date "2019-01-01"
     *
     * @param string $value
     *
     * @return DatesRange
     * @throws InvalidArgumentException
     */
    public function parseDates(string $value): DatesRange
    {
        if (strpos($value, ':') === false) {
            $beginValue = $endValue = trim($value);
        } else {
            [$beginValue, $endValue] = array_map('trim', explode(':', $value));
        }

        return new DatesRange(
            $beginValue ? $this->createDate($beginValue) : null,
            $endValue ? $this->createDate($endValue) : null
        );
    }

    /**
     * @param string $date
     *
     * @return DateTimeImmutable
     * @throws InvalidArgumentException
     */
    private function createDate(string $date): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($date);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Can not parse date \"$date\"", 0, $e);
        }
    }
}