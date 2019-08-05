<?php

declare(strict_types=1);

namespace App\Parser\Data;

use DateTimeImmutable;

/**
 * Parsed transaction
 */
final class ParsedTransaction
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
     * @var TransactionAmount|null
     */
    private $amount;

    /**
     * @var string|null
     */
    private $sourceAccount;

    /**
     * @var ParsedTransactionPart[]
     */
    private $parts = [];

    /**
     * @param DateTimeImmutable $date
     */
    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param TransactionAmount $amount
     */
    public function setAmount(TransactionAmount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param string $sourceAccount
     */
    public function setSourceAccount(string $sourceAccount): void
    {
        $this->sourceAccount = $sourceAccount;
    }

    /**
     * Add transaction parts
     *
     * @param ParsedTransactionPart ...$parts
     */
    public function split(ParsedTransactionPart ...$parts): void
    {
        $this->parts = $parts;
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
     * @return TransactionAmount|null
     */
    public function getAmount(): ?TransactionAmount
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function getSourceAccount(): ?string
    {
        return $this->sourceAccount;
    }

    /**
     * @return ParsedTransactionPart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}