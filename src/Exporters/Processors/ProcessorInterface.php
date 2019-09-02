<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors;

use vvvitaly\txs\Core\Export\Data\Transaction;

/**
 * Process exported transaction
 */
interface ProcessorInterface
{
    /**
     * Process transaction.
     *
     * @param Transaction $transaction
     */
    public function process(Transaction $transaction): void;
}