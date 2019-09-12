<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

/**
 * Format cells with numbers contains comma instead of decimal point.
 */
final class FormatNumbersDecorator implements CsvReaderInterface
{
    /**
     * @var \vvvitaly\txs\Csv\CsvReaderInterface
     */
    private $reader;

    /**
     * @param \vvvitaly\txs\Csv\CsvReaderInterface $reader
     */
    public function __construct(CsvReaderInterface $reader)
    {
        $this->reader = $reader;
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

        return $this->format($row);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->reader->close();
    }

    /**
     * Format numbers
     *
     * @param array $row
     *
     * @return array
     */
    private function format(array $row): array
    {
        $map = static function ($value) {
            if (preg_match('/^-?\d+,?\d*$/u', $value) === 1) {
                return str_replace(',', '.', $value);
            }

            return $value;
        };

        return array_map($map, $row);
    }
}