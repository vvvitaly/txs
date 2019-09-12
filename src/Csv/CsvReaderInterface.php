<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

/**
 * Read CSV document row by row. Document is read when the current call of readRow returns null.
 */
interface CsvReaderInterface
{
    /**
     * Called before reading.
     */
    public function open(): void;

    /**
     * Read the next row from the CSV and returns the array of columns. If this method returns null, the all rows has
     * been read.
     *
     * @return array|null
     * @throws CsvReadException
     */
    public function readRow(): ?array;

    /**
     * Called after reading.
     */
    public function close(): void;
}