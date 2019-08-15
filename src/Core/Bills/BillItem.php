<?php

declare(strict_types=1);

namespace App\Core\Bills;

/**
 * One transaction part
 */
final class BillItem
{
    /**
     * @var string|null
     */
    private $description;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @param string|null $description
     * @param Amount $amount
     */
    public function __construct(?string $description, Amount $amount)
    {
        $this->description = $description;
        $this->amount = $amount;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }
}