<?php

declare(strict_types=1);

namespace App\Vmestecard;

use DateTimeImmutable;

/**
 * Vmestacard transaction DTO
 */
final class Transaction
{
    /**
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $chequeNumber;

    /**
     * @var TransactionItem[]
     */
    public $items;
}