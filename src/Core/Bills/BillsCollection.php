<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

use ArrayObject;
use IteratorIterator;

/**
 * Collection of transactions parsed from some source
 */
final class BillsCollection extends IteratorIterator
{
    /**
     * @param Bill ...$bill
     */
    public function __construct(Bill ...$bill)
    {
        parent::__construct(new ArrayObject($bill));
    }

    /**
     * @inheritDoc
     */
    public function current(): Bill
    {
        return parent::current();
    }
}