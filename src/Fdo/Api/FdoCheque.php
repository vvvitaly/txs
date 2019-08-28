<?php

declare(strict_types=1);

namespace App\Fdo\Api;

use DateTimeImmutable;

/**
 * FDO cheque DTO
 */
final class FdoCheque
{
    /**
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * @var float
     */
    public $totalAmount;

    /**
     * @var string Cheque number
     */
    public $number;

    /**
     * @var string Buying place, uses for descriptions
     */
    public $place;

    /**
     * @var FdoChequeItem[] List of cheque items
     */
    public $items = [];
}