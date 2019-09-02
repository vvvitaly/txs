<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors;

use vvvitaly\txs\Core\Export\Data\Transaction;

/**
 * Resolve transactions IDs. It users global counter for all transactions.
 */
final class AutoIdCounter implements ProcessorInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    private $initialValue;

    /**
     * @var int
     */
    private $counter;

    /**
     * @param string $prefix text prefix for ID
     * @param int $initialValue initial counter value
     */
    public function __construct(string $prefix = '', int $initialValue = 0)
    {
        $this->prefix = $prefix;
        $this->initialValue = $initialValue;
    }

    /**
     * @inheritDoc
     */
    public function process(Transaction $transaction): void
    {
        $transaction->id = $this->prefix . $this->getNextId();
    }

    /**
     * Generate the next counter value.
     *
     * @return int
     */
    private function getNextId(): int
    {
        if ($this->counter === null) {
            $this->counter = $this->initialValue;
        } else {
            $this->counter++;
        }

        return  $this->counter;
    }
}