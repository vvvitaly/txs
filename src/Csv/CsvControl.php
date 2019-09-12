<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

/**
 * Configuration for CSV source
 */
final class CsvControl
{
    /**
     * @var string
     */
    public $csvSeparator = ',';

    /**
     * @var string
     */
    public $enclosure = '"';

    /**
     * @var string
     */
    public $escape = '\\';
}