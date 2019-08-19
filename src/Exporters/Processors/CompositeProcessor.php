<?php

declare(strict_types=1);

namespace App\Exporters\Processors;

use App\Core\Export\Data\Transaction;

/**
 * Process transactions with list of objects.
 */
final class CompositeProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(ProcessorInterface ...$processors)
    {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function process(Transaction $transaction): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($transaction);
        }
    }
}