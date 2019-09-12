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
    public $csvSeparator;

    /**
     * @var string
     */
    public $enclosure;

    /**
     * @var string
     */
    public $escape;

    /**
     * @param string $csvSeparator
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct(string $csvSeparator = ',', string $enclosure = '"', string $escape = '\\')
    {
        $this->csvSeparator = $csvSeparator;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }
}