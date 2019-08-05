<?php

declare(strict_types=1);

namespace App\Parser\Data;

use IteratorIterator;

/**
 * Collection of transactions parsed from some source
 */
final class ParsedTransactionsList extends IteratorIterator
{
    /**
     * @param ParsedTransaction ...$transactions
     */
    public function __construct(ParsedTransaction ...$transactions)
    {
        parent::__construct(
            (static function() use ($transactions) {
                yield from $transactions;
            })()
        );
    }

    /**
     * @inheritDoc
     */
    public function current(): ParsedTransaction
    {
        return parent::current();
    }

    /**
     * Convert collection to PHP array
     *
     * @return ParsedTransaction[]
     */
    public function toArray(): array
    {
        return iterator_to_array($this, false);
    }
}