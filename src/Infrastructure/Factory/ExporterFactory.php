<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Factory;

use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Exporters\BillExporter;
use vvvitaly\txs\Exporters\Processors\AutoIdCounter;
use vvvitaly\txs\Exporters\Processors\CompositeProcessor;
use vvvitaly\txs\Exporters\Processors\CurrencyNormalizer;
use vvvitaly\txs\Exporters\Processors\DescriptionAsAccount;

/**
 * Default factory for bills exporter
 */
final class ExporterFactory
{
    /**
     * @var BillExporterInterface
     */
    private static $defaultExporter;

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
    public static function getBillsExporter(): BillExporterInterface
    {
        if (!self::$defaultExporter) {
            self::$defaultExporter = new BillExporter(
                new CompositeProcessor(
                    new AutoIdCounter(),
                    new DescriptionAsAccount(),
                    new CurrencyNormalizer()
                )
            );
        }

        return self::$defaultExporter;
    }
}