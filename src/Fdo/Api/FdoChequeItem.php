<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api;

/**
 * Item DTO
 */
final class FdoChequeItem
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $amount;

    /**
     * @param string $name
     * @param float $amount
     */
    public function __construct(string $name, float $amount)
    {
        $this->amount = $amount;
        $this->name = $name;
    }
}