<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters;

/**
 * Configuration object for CSV writer
 */
final class CsvWriterConfig
{
    /**
     * @var string
     */
    public $dateFormat = 'Y-m-d';

    /**
     * @var string
     */
    public $decimalDelimiter = '.';

    /**
     * @var bool
     */
    public $withHeader = true;

    /**
     * @var string
     */
    public $csvSeparator = ',';

    /**
     * Create default config instance.
     *
     * @return CsvWriterConfig
     */
    public static function defaultConfig(): CsvWriterConfig
    {
        return new static();
    }
}