<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use vvvitaly\txs\Exporters\Processors\DescriptionNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\AliasNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\BrandsNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\ContractionsNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\SpacesNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\UnitsNormalizer;
use vvvitaly\txs\Exporters\Processors\Normalizers\WordsNormalizer;

/**
 * Create description normalizer processor
 * @see DescriptionNormalizer
 * @see AliasNormalizer
 */
final class DescriptionNormalizerFactory
{
    /**
     * @var array
     */
    private $aliasesMap;

    /**
     * @var DescriptionNormalizer
     */
    private $instance;

    /**
     * @param array $aliasesMap
     */
    public function __construct(array $aliasesMap)
    {
        $this->aliasesMap = $aliasesMap;
    }

    /**
     * @return DescriptionNormalizer
     */
    public function getNormalizer(): DescriptionNormalizer
    {
        if (!$this->instance) {
            $this->instance = new DescriptionNormalizer([
                new AliasNormalizer($this->aliasesMap),
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