<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

use Webmozart\Assert\Assert;

/**
 * Amount of transaction with currency
 */
final class Amount
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
    public function __construct(float $value, ?string $currency = null)
    {
        Assert::greaterThanEq($value, 0, 'Bill amount must be positive: ' . $value);

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