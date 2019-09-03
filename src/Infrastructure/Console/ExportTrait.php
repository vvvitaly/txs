<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
                true
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
        $isAppendTransactions = $input->getOption('append');
        $fileMode = $isAppendTransactions ? 'ab' : 'wb';
        $outFileName = $input->getOption('csv') ?: 'bills-' . date('Y-m-d') . '.csv';
        $outCsv = new SplFileObject($outFileName, $fileMode);
        if (!$outCsv->isWritable()) {
            throw new InvalidArgumentException("Can not write to \"{$outFileName}\"");
        }

        $csvConfig = new CsvWriterConfig();
        $csvConfig->withHeader = !$isAppendTransactions || !$outCsv->getSize();
        $writer = new MultiSplitCsvWriter($outCsv, $csvConfig);

        $bills = $source->read();

        if ($output->isVerbose()) {
            $this->getHelper('bills_printer')->printBills($bills, $output);
        }

        $exporter = new CollectionExporter($billExporter);
        $transactions = $exporter->export($bills);
        $writer->write($transactions);

        $resultFile = $outCsv->getRealPath();
        $output->writeln("Transactions were exported into <info>\"{$resultFile}\"</info>");
    }
}