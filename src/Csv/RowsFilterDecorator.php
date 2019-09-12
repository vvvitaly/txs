<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

/**
 * This decorator allows to skip read rows from the CSV.
 * If the filter function returns true, the given rows passed to source. Otherwise it will be skipped.
 * The filter function has signature:
 * ```
 * function (array $row): bool;
 * ```
 */
final class RowsFilterDecorator implements CsvReaderInterface
{
    /**
     * @var \vvvitaly\txs\Csv\CsvReaderInterface
     */
    private $reader;

    /**
     * @var callable
     */
    private $filter;

    /**
     * @param \vvvitaly\txs\Csv\CsvReaderInterface $reader
     * @param callable $filter
     */
    public function __construct(CsvReaderInterface $reader, callable $filter)
    {
        $this->reader = $reader;
        $this->filter = $filter;
    }

    /**
     * @inheritDoc
     */
    public function open(): void
    {
        $this->reader->open();
    }

    /**
     * @inheritDoc
     */
    public function readRow(): ?array
    {
        $row = $this->reader->readRow();

        if ($row === null) {
            return null;
        }

        $filter = $this->filter;
        if ($filter($row)) {
            return $row;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->reader->close();
    }

}