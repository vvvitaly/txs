<?php

declare(strict_types=1);

namespace App\Parser\Data;

/**
 * Amount of transaction with currency
 */
final class TransactionAmount
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param float $value
     * @param string|null $currency
     */
    public function __construct(float $value, ?string $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}