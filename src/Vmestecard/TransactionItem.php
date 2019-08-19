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
}