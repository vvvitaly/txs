<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Exporters\BillExporter;
use vvvitaly\txs\Exporters\Processors\AutoIdCounter;
use vvvitaly\txs\Exporters\Processors\CompositeProcessor;
use vvvitaly\txs\Exporters\Processors\CurrencyNormalizer;
use vvvitaly\txs\Exporters\Processors\DescriptionAlias;
use vvvitaly\txs\Exporters\Processors\DescriptionAsAccount;

/**
 * Default factory for bills exporter
 */
final class ExporterFactory
{
    /**
     * @var DescriptionNormalizerFactory
     */
    private $descriptionNormalizerFactory;

    /**
     * @var BillExporterInterface
     */
    private $billsExporter;

    /**
     * @var array
     */
    private $aliasesMap;

    /**
     * @param array $aliasesMap Aliases map for DescriptionAlias processor
     * @param DescriptionNormalizerFactory $descriptionNormalizerFactory
     */
    public function __construct(
        array $aliasesMap,
        DescriptionNormalizerFactory $descriptionNormalizerFactory
    )
    {
        $this->aliasesMap = $aliasesMap;
        $this->descriptionNormalizerFactory = $descriptionNormalizerFactory;
    }

    /**
     * Default exporter implementation. Includes following processors:
     *  - AutoIdCounter
     *  - DescriptionAsAccount
     *
     * @return BillExporterInterface
     *
     * @see BillExporter
     * @see AutoIdCounter
     * @see DescriptionAsAccount
     */
    public function getBillsExporter(): BillExporterInterface
    {
        if (!$this->billsExporter) {
            $this->billsExporter = new BillExporter(
                new CompositeProcessor(
                    new AutoIdCounter(),
                    $this->descriptionNormalizerFactory->getNormalizer(),
                    new DescriptionAlias($this->aliasesMap),
                    new DescriptionAsAccount(),
                    new CurrencyNormalizer()
                )
            );
        }

        return $this->billsExporter;
    }
}