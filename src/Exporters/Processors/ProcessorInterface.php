<?php

declare(strict_types=1);

namespace App\Exporters\Processors;

use App\Core\Export\Data\Transaction;

/**
 * Process exported transaction
 * @todo refactor
 */
interface ProcessorInterface
{
    /**
     * Set the next processor in chain.
     *
     * @param ProcessorInterface $processor
     *
     * @return mixed
     */
    public function setNext(ProcessorInterface $processor): void;
    
    /**
     * Process transaction.
     *
     * @param Transaction $transaction
     */
    public function process(Transaction $transaction): void;
}