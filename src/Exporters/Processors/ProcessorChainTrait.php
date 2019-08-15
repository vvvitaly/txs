<?php

declare(strict_types=1);

namespace App\Exporters\Processors;

use App\Core\Export\Data\Transaction;

/**
 * Chain of responsibility mixin
 */
trait ProcessorChainTrait
{
    /**
     * @var ProcessorInterface
     */
    private $next;

    /**
     * @param ProcessorInterface $processor
     * @see ProcessorInterface::setNext
     */
    public function setNext(ProcessorInterface $processor): void
    {
        $this->next = $processor;
    }

    /**
     * Run next processor in chain if exists.
     *
     * @param Transaction $transaction
     */
    private function next(Transaction $transaction): void
    {
        if ($this->next) {
            $this->next->process($transaction);
        }
    }
}