<?php

declare(strict_types=1);

namespace App\Parser\Data;

/**
 * One transaction part
 */
final class ParsedTransactionPart
{
    /**
     * @var string|null
     */
    private $description;

    /**
     * @var TransactionAmount
     */
    private $amount;

    /**
     * @param string|null $description
     * @param TransactionAmount $amount
     */
    public function __construct(?string $description, TransactionAmount $amount)
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
     * @return TransactionAmount
     */
    public function getAmount(): TransactionAmount
    {
        return $this->amount;
    }
}