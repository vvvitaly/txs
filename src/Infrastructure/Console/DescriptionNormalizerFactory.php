<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use vvvitaly\txs\Exporters\Processors\DescriptionNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\BrandsNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\ContractionsNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\SpacesNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\UnitsNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\WordsNormalizer;

/**
 * Create description normalizer processor
 * @see DescriptionNormalizer
 * @see DescriptionAlias
 */
final class DescriptionNormalizerFactory
{
    /**
     * @var DescriptionNormalizer
     */
    private $instance;

    /**
     * @return DescriptionNormalizer
     */
    public function getNormalizer(): DescriptionNormalizer
    {
        if (!$this->instance) {
            $this->instance = new DescriptionNormalizer([
                new UnitsNormalizer(),
                new BrandsNormalizer(),
                new ContractionsNormalizer(),
                new WordsNormalizer(),
                new SpacesNormalizer(),
            ]);
        }

        return $this->instance;
    }
}