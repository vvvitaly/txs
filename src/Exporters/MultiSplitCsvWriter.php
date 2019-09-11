<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters;

use SplFileObject;
use vvvitaly\txs\Core\Export\Data\TransactionCollection;

/**
 * Write exported transaction to CSV file (multi-split mode)
 */
final class MultiSplitCsvWriter
{
    /**
     * @var SplFileObject
     */
    private $file;

    /**
     * @var CsvWriterConfig
     */
    private $config;

    /**
     * @param SplFileObject $file
     * @param CsvWriterConfig|null $config
     */
    public function __construct(SplFileObject $file, ?CsvWriterConfig $config = null)
    {
        $this->file = $file;
        $this->config = $config ?? CsvWriterConfig::defaultConfig();
    }

    /**
     * @return SplFileObject
     */
    public function getFile(): SplFileObject
    {
        return $this->file;
    }

    /**
     * Write exported transactions to the given file.
     * The CSV has following format:
     *  "Date","TxID","Bill No","Account","Tx Desc","Amount","Currency","Memo"
     * Transactions are identified by ID attribute. Each split prints on separate line with the same ID
     *
     * @param TransactionCollection $transactions
     */
    public function write(TransactionCollection $transactions): void
    {
        $this->file->setCsvControl($this->config->csvSeparator);

        if ($this->config->withHeader) {
            $this->file->fputcsv([
                'Date',
                'TxID',
                'Bill No',
                'Account',
                'Tx Desc',
                'Amount',
                'Currency',
                'Memo',
            ]);
        }

        foreach ($transactions as $transaction) {
            $this->file->fputcsv([
                $transaction->date->format($this->config->dateFormat),
                $transaction->id,
                $transaction->num,
                $transaction->account,
                $transaction->description,
                $this->formatAmount($transaction->amount),
                $transaction->currency,
                null,
            ]);

            foreach ($transaction->splits as $split) {
                $this->file->fputcsv([
                    null,
                    $transaction->id,
                    null,
                    $split->account,
                    null,
                    $this->formatAmount($split->amount),
                    null,
                    $split->memo,
                ]);
            }
        }
    }

    /**
     * Format amount value according to configuration
     *
     * @param float $value
     *
     * @return string
     */
    private function formatAmount(float $value): string
    {
        return number_format($value, 2, $this->config->decimalDelimiter, '');
    }
}