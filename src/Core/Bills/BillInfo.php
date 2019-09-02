<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

use DateTimeImmutable;

/**
 * Bill meta information
 */
final class BillInfo
{
    /**
     * @var DateTimeImmutable|null
     */
    private $date;

    /**
     * @var string|null
     */
    private $description;

    /**
     * Bill number
     *
     * @var string|null
     */
    private $number;

    /**
     * @param DateTimeImmutable|null $date
     * @param string|null $description
     * @param string|null $number
     */
    public function __construct(?DateTimeImmutable $date = null, ?string $description = null, ?string $number = null)
    {
        $this->date = $date;
        $this->description = $description;
        $this->number = $number;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }
}