<?php

declare(strict_types=1);

namespace App\Ofd\Api;

use DateTimeImmutable;

/**
 * OFD cheque DTO
 */
final class OfdCheque
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
     * @var array List of cheque items in format [name, amount]
     */
    public $items = [];
}