<?php

declare(strict_types=1);

namespace App\Vmestecard;

/**
 * One item from transaction
 */
final class TransactionItem
{
    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $description;

    /**
     * @param float $amount
     * @param string $description
     */
    public function __construct(float $amount, string $description)
    {
        $this->amount = $amount;
        $this->description = $description;
    }
}