<?php

declare(strict_types=1);

namespace App\Core\Export\Data;

use ArrayObject;
use IteratorIterator;

/**
 * Typed transaction collection DTO
 */
final class TransactionCollection extends IteratorIterator
{
    /**
     * @param Transaction ...$transaction
     */
    public function __construct(Transaction ...$transaction)
    {
        parent::__construct(new ArrayObject($transaction));
    }

    /**
     * @inheritDoc
     */
    public function current(): Transaction
    {
        return parent::current();
    }
}