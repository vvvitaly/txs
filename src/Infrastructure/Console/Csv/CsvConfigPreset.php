<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console\Csv;

use vvvitaly\txs\Csv\CsvControl;

/**
 * CSV document structure and format
 */
final class CsvConfigPreset
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var CsvControl
     * @see \vvvitaly\txs\Csv\CsvSource
     */
    public $control;

    /**
     * @var string|null
     * @see \vvvitaly\txs\Csv\EncodeDecorator
     */
    public $encoding;

    /**
     * @var callable|null
     * @see \vvvitaly\txs\Csv\RowsFilterDecorator
     */
    public $rowsFilter;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->control = new CsvControl();
    }

    /**
     * @param array $columns
     *
     * @return CsvConfigPreset
     */
    public function setColumns(array $columns): CsvConfigPreset
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param CsvControl $control
     *
     * @return CsvConfigPreset
     */
    public function setControl(CsvControl $control): CsvConfigPreset
    {
        $this->control = $control;

        return $this;
    }

    /**
     * @param string $encoding
     *
     * @return CsvConfigPreset
     */
    public function setEncoding(string $encoding): CsvConfigPreset
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @param callable $rowsFilter
     *
     * @return CsvConfigPreset
     */
    public function setRowsFilter(callable $rowsFilter): CsvConfigPreset
    {
        $this->rowsFilter = $rowsFilter;

        return $this;
    }
}