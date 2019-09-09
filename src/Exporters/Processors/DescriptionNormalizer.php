<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors;

use vvvitaly\txs\Core\Export\Data\Transaction;

/**
 * Normalize transaction description and items. It runs multiple normalizers, passed to constructor. Each normalizer
 * has signature
 *      function (?string $text): ?string;
 */
final class DescriptionNormalizer implements ProcessorInterface
{
    /**
     * @var callable[]
     */
    private $normalizers;

    /**
     * @param callable[] $normalizers
     */
    public function __construct(array $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    /**
     * @inheritDoc
     */
    public function process(Transaction $transaction): void
    {
        foreach ($this->normalizers as $normalizer) {
            $transaction->description = $normalizer($transaction->description);
            foreach ($transaction->splits as $split) {
                $split->memo = $normalizer($split->memo);
            }
        }
    }
}