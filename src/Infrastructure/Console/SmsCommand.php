<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use InvalidArgumentException;
use SimpleXMLElement;
use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Exporters\CollectionExporter;
use vvvitaly\txs\Exporters\CsvWriterConfig;
use vvvitaly\txs\Exporters\MultiSplitCsvWriter;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\SmsBackupXMLSource;

/**
 * Export bills from SMS saved in "SMS backup & restore" application (XML backup)
 */
final class SmsCommand extends Command
{
    /**
     * @var MessageParserInterface
     */
    private $smsParser;

    /**
     * @var BillExporterInterface
     */
    private $billExporter;

    /**
     * @param MessageParserInterface $smsParser
     * @param BillExporterInterface $billExporter
     */
    public function __construct(MessageParserInterface $smsParser, BillExporterInterface $billExporter)
    {
        parent::__construct();

        $this->smsParser = $smsParser;
        $this->billExporter = $billExporter;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $datesHelp = DatesRangeHelper::getHelp();

        $this
            ->setName('sms')
            ->setDescription('Export bills from SMS saved in "SMS backup & restore" application (XML backup)')
            ->setDefinition([
                new InputArgument('xml', InputArgument::REQUIRED, 'XML backup path'),
                new InputArgument('dates', InputArgument::REQUIRED, 'SMS export dates range'),
                new InputOption(
                    'csv',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'CSV file to export',
                    'bills-' . date('Ymd') . '.csv'
                ),
                new InputOption('append', null, InputOption::VALUE_OPTIONAL, 'Do not rewrite existing CSV file', true),
            ])
            ->setHelp(
                <<<EOS
The <info>sms</info> command reads an XML file created in "SMS backup & restore" application for Android. It processes
only messages was received in the given dates range.

{$datesHelp}
EOS
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $xml = $this->loadXML($input->getArgument('xml'));
        $datesRange = $this->getHelper('datesRange')->parseDates($input->getArgument('dates'));

        $isAppendTransactions = $input->getOption('append');
        $fileMode = $isAppendTransactions ? 'ab' : 'wb';
        $outFileName = $input->getOption('csv') ?: 'bills-' . date('Y-m-d') . '.csv';
        $outCsv = new SplFileObject($outFileName, $fileMode);
        if (!$outCsv->isWritable()) {
            throw new InvalidArgumentException("Can not write to \"{$outFileName}\"");
        }

        $csvConfig = new CsvWriterConfig();
        $csvConfig->withHeader = !$outCsv->getSize();
        $writer = new MultiSplitCsvWriter($outCsv, $csvConfig);

        $source = new SmsBackupXMLSource($xml, $datesRange, $this->smsParser);
        $bills = $source->read();

        if ($output->isVerbose()) {
            $this->getHelper('bills_printer')->printBills($bills, $output);
        }

        $exporter = new CollectionExporter($this->billExporter);
        $transactions = $exporter->export($bills);
        $writer->write($transactions);

        $resultFile = $outCsv->getRealPath();
        $output->writeln("Transactions were exported into <info>\"{$resultFile}\"</info>");

        return 1;
    }

    /**
     * @param string $fileName
     *
     * @return SimpleXMLElement
     * @throws InvalidArgumentException
     */
    private function loadXML(string $fileName): SimpleXMLElement
    {
        if (!is_file($fileName)) {
            throw new InvalidArgumentException("File \"{$fileName}\" doesn't exists");
        }

        $isInternalErrorsUsed = libxml_use_internal_errors(true);

        $xml = simplexml_load_string(file_get_contents($fileName));

        if (!$xml) {
            $error = libxml_get_last_error();
            throw new InvalidArgumentException("Can not load XML file \"{$fileName}\": " . $error);
        }

        libxml_use_internal_errors($isInternalErrorsUsed);

        return $xml;
    }
}