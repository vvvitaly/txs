<?php

declare(strict_types=1);

namespace App\Libs\Date;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

/**
 * Work with interval of dates. It's set by the begin and the end dates of this interval. Both dates might be set, or
 * only one of them (begin or date, but not any of them).
 */
final class DateRange
{
    /**
     * @var DateTimeImmutable|null
     */
    private $begin;

    /**
     * @var DateTimeImmutable|null
     */
    private $end;

    /**
     * @param DateTimeImmutable|null $begin
     * @param DateTimeImmutable|null $end
     */
    public function __construct(?DateTimeImmutable $begin = null, ?DateTimeImmutable $end = null)
    {
        Assert::true($begin === null ^ $end === null || $begin !== null && $end !== null,
            'At least one of the dates must be set');

        if ($begin && $end) {
            Assert::true($begin === null || $begin < $end, 'Begin date must be less than end date');
        }

        $this->begin = $begin;
        $this->end = $end;
    }

    /**
     * Check if the given date is in the specified range.
     *
     * @param DateTimeImmutable $testingDate
     *
     * @return bool
     */
    public function contains(DateTimeImmutable $testingDate): bool
    {
        return
            ($this->begin === null || $testingDate >= $this->begin) &&
            ($this->end === null || $testingDate <= $this->end);
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getBegin(): ?DateTimeImmutable
    {
        return $this->begin;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }
}