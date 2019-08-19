<?php

declare(strict_types=1);

namespace App\Exporters\Processors;

use App\Core\Export\Data\Transaction;

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