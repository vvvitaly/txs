<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

use DateTimeImmutable;
use Exception;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Core\Source\BillSourceInterface;
use vvvitaly\txs\Core\Source\SourceReadException;
use Webmozart\Assert\Assert;

/**
 * Obtain bills from some CSV document. It configures with list of columns (see CsvColumn constants) in the same
 * order as they placed in the document. If one of the columns should be skipped the constant COLUMN_IGNORE (or just
 * null) should be used. CSV rows obtained from the document with reader instance.
 *
 * @see CsvColumn
 */
final class CsvSource implements BillSourceInterface
{
    /**
     * @var array
     */
    private $columns;

    /**
     * @var CsvReaderInterface
     */
    private $reader;

    /**
     * @param array $columns
     * @param CsvReaderInterface $reader
     */
    public function __construct(array $columns, CsvReaderInterface $reader)
    {
        $this->columns = array_flip($columns);
        $this->reader = $reader;

        Assert::keyExists($this->columns, CsvColumn::DATE, 'date column is required');
        Assert::keyExists($this->columns, CsvColumn::ACCOUNT, 'account column is required');
        Assert::keyExists($this->columns, CsvColumn::AMOUNT, 'amount column is required');
    }

    /**
     * @inheritDoc
     */
    public function read(): BillsCollection
    {
        $this->reader->open();

        $bills = [];

        while (true) {

            try {
                $row = $this->reader->readRow();
            } catch (CsvReadException $exception) {
                $this->reader->close();
                throw new SourceReadException('Can not read CSV document', 0, $exception);
            }

            if ($row === null) {
                break;
            }

            if ($row === []) {
                continue;
            }

            $bills[] = $this->parseBill($row);
        }

        $this->reader->close();

        return new BillsCollection(...$bills);
    }

    /**
     * Parse bill from the CSV row
     *
     * @param array $row
     *
     * @return Bill
     * @throws SourceReadException
     */
    private function parseBill(array $row): Bill
    {
        $dateValue = $this->extractColumnValue($row, CsvColumn::DATE);
        if (!$dateValue) {
            throw new SourceReadException('Can not obtain date');
        }

        try {
            $date = new DateTimeImmutable($dateValue);
        } catch (Exception $e) {
            throw new SourceReadException("Can not parse date: \"{$dateValue}\"", 0, $e);
        }

        $amount = (float)$this->extractColumnValue($row, CsvColumn::AMOUNT);
        $composer = $amount > 0
            ? Composer::incomeBill()
            : Composer::expenseBill();

        return $composer
            ->setAmount(abs($amount))
            ->setCurrency($this->extractColumnValue($row, CsvColumn::CURRENCY))
            ->setAccount($this->extractColumnValue($row, CsvColumn::ACCOUNT))
            ->setDate($date)
            ->setDescription($this->extractColumnValue($row, CsvColumn::DESCRIPTION))
            ->getBill();
    }

    /**
     * @param array $row
     * @param string $columnName
     *
     * @return string|null
     */
    private function extractColumnValue(array $row, string $columnName): ?string
    {
        if (!isset($this->columns[$columnName])) {
            return null;
        }

        return $row[$this->columns[$columnName]] ?? null;
    }
}