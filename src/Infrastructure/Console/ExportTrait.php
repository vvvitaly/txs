<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Core\Source\BillSourceInterface;
use vvvitaly\txs\Exporters\CollectionExporter;
use vvvitaly\txs\Exporters\CsvWriterConfig;
use vvvitaly\txs\Exporters\MultiSplitCsvWriter;

/**
 * Export source's bills into the given CSV
 */
trait ExportTrait
{
    /**
     * Add options related with CSV.
     *
     * @param InputDefinition $definition
     */
    private function configureCsvOptions(InputDefinition $definition): void
    {
        $definition->addOptions([
            new InputOption(
                'read',
                null,
                InputOption::VALUE_NONE,
                'Do not write CSV, just read bills from the source'
            ),
            new InputOption(
                'csv',
                null,
                InputOption::VALUE_REQUIRED,
                'CSV file to export',
                'bills-' . date('Ymd') . '.csv'
            ),
            new InputOption(
                'append',
                null,
                InputOption::VALUE_OPTIONAL,
                'Do not rewrite existing CSV file',
                false
            ),
        ]);
    }

    /**
     * Make export
     *
     * @param BillSourceInterface $source
     * @param BillExporterInterface $billExporter
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     */
    private function export(
        BillSourceInterface $source,
        BillExporterInterface $billExporter,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $isJustRead = $input->getOption('read');

        if ($isJustRead) {
            $this->exportOutput($source, $output);

            return;
        }

        $writer = $this->createWriter($input);
        $this->exportCsv($source, $billExporter, $writer, $output);
    }

    /**
     * @param BillSourceInterface $source
     * @param OutputInterface $output
     */
    private function exportOutput(BillSourceInterface $source, OutputInterface $output): void
    {
        $bills = $source->read();
        $this->printBills($bills, $output);
    }

    /**
     * Print bills collection
     *
     * @param BillsCollection $bills
     * @param OutputInterface $output
     */
    private function printBills(BillsCollection $bills, OutputInterface $output): void
    {
        $this->getHelper('bills_printer')->printBills($bills, $output);
    }

    /**
     * @param InputInterface $input
     *
     * @return MultiSplitCsvWriter
     */
    private function createWriter(InputInterface $input): MultiSplitCsvWriter
    {
        $isAppendTransactions = $input->getOption('append');
        $fileMode = $isAppendTransactions ? 'ab' : 'wb';
        $outFileName = $input->getOption('csv') ?: 'bills-' . date('Y-m-d') . '.csv';
        $outCsv = new SplFileObject($outFileName, $fileMode);
        if (!$outCsv->isWritable()) {
            throw new InvalidArgumentException("Can not write to \"{$outFileName}\"");
        }

        $csvConfig = new CsvWriterConfig();
        $csvConfig->withHeader = !$isAppendTransactions || !$outCsv->getSize();

        return new MultiSplitCsvWriter($outCsv, $csvConfig);
    }

    /**
     * @param BillSourceInterface $source
     * @param BillExporterInterface $billExporter
     * @param MultiSplitCsvWriter $writer
     * @param OutputInterface $output
     */
    private function exportCsv(
        BillSourceInterface $source,
        BillExporterInterface $billExporter,
        MultiSplitCsvWriter $writer,
        OutputInterface $output
    ): void {
        $bills = $source->read();

        if ($output->isVerbose()) {
            $this->printBills($bills, $output);
        }

        $exporter = new CollectionExporter($billExporter);
        $transactions = $exporter->export($bills);
        $writer->write($transactions);

        $resultFile = $writer->getFile()->getRealPath();
        $output->writeln("Transactions were exported into <info>\"{$resultFile}\"</info>");
    }
}