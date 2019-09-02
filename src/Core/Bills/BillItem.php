<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

use Webmozart\Assert\Assert;

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
     * @param string $description
     * @param Amount $amount
     */
    public function __construct(string $description, Amount $amount)
    {
        Assert::notEmpty($description);

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