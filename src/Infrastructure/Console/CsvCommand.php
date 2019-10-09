<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Csv\CsvReaderInterface;
use vvvitaly\txs\Csv\CsvSource;
use vvvitaly\txs\Csv\Encode\MbEncoder;
use vvvitaly\txs\Csv\EncodeDecorator;
use vvvitaly\txs\Csv\FormatNumbersDecorator;
use vvvitaly\txs\Csv\RowsFilterDecorator;
use vvvitaly\txs\Csv\SplFileObjectReader;
use vvvitaly\txs\Infrastructure\Console\Csv\CsvConfigPreset;

/**
 * Export bills from CSV report
 */
final class CsvCommand extends Command
{
    use ExportTrait;

    /**
     * @var BillExporterInterface
     */
    private $billExporter;

    /**
     * @var CsvConfigPreset[]
     */
    private $presets;

    /**
     * @param BillExporterInterface $billExporter
     * @param array $presets
     */
    public function __construct(BillExporterInterface $billExporter, array $presets)
    {
        $this->presets = [];
        foreach ($presets as $preset) {
            $this->presets[$preset->name] = $preset;
        }

        parent::__construct();

        $this->billExporter = $billExporter;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $presets = implode(', ', array_keys($this->presets));

        $this
            ->setName('csv')
            ->setDescription('Export bills from CSV report')
            ->setDefinition([
                new InputArgument('csv', InputArgument::REQUIRED, 'CSV document path'),
                new InputOption(
                    'preset',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'CSV configuration preset. Possible values: ' . $presets
                ),
            ])
            ->setHelp(
                <<<EOS
The <info>csv</info> command reads an CSV file contains history of operations for some account. Such CSV must contain
at least three columns:
* operation date
* account name
* operation amount

Optional columns are:
* currency name
* operation description

Order of this columns and its format specified with preset parameter. Existing presets: {$presets} 
EOS
            );

        $this->configureCsvOptions($this->getDefinition());
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csv = $input->getArgument('csv');

        if (!is_file($csv) || !is_readable($csv)) {
            throw new InvalidArgumentException("File \"{$csv}\" does not exists or not readable");
        }

        $presetName = $input->getOption('preset');
        if (!$presetName || !isset($this->presets[$presetName])) {
            throw new InvalidArgumentException("Unknown preset \"{$presetName}\"");
        }
        $preset = $this->presets[$presetName];

        $reader = $this->buildReader($csv, $preset);

        $source = new CsvSource($preset->columns, $reader);

        $this->export($source, $this->billExporter, $input, $output);

        return 1;
    }

    /**
     * Get CSV reader instance
     *
     * @param string $filename
     * @param CsvConfigPreset $preset
     *
     * @return CsvReaderInterface
     */
    private function buildReader(string $filename, CsvConfigPreset $preset): CsvReaderInterface
    {
        $reader = new SplFileObjectReader(
            new SplFileObject($filename),
            $preset->control
        );

        if ($preset->rowsFilter) {
            $reader = new RowsFilterDecorator($reader, $preset->rowsFilter);
        }

        if ($preset->encoding) {
            $reader = new EncodeDecorator($reader, new MbEncoder(), $preset->encoding);
        }

        $reader = new FormatNumbersDecorator($reader);

        return $reader;
    }
}