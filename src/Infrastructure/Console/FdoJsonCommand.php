<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Fdo\FdoJsonSource;

/**
 * Export bills from FDO bills checking application JSON report.
 */
final class FdoJsonCommand extends Command
{
    use ExportTrait;

    /**
     * @var BillExporterInterface
     */
    private $billExporter;

    /**
     * @param BillExporterInterface $billExporter
     */
    public function __construct(BillExporterInterface $billExporter)
    {
        parent::__construct();

        $this->billExporter = $billExporter;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('fdo.json')
            ->setDescription('Export bills from FDO bills checking application JSON report')
            ->setDefinition([
                new InputArgument('json', InputArgument::REQUIRED, 'JSON report file path'),
                new InputOption(
                    'account',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Default account name for operations of this user',
                    '_UNASSIGNED_'
                ),
            ])
            ->setHelp(
                <<<EOS
The <info>fdo.json</info> command obtains bills from the report generated by FDO bills checking application. 
<bold>It exports ALL data from the given file</bold>
EOS
            );

        $this->configureCsvOptions($this->getDefinition());
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $json = $this->loadJsonData($input->getArgument('json'));
        $account = $input->getOption('account');

        $source = new FdoJsonSource($json, $account);
        $this->export($source, $this->billExporter, $input, $output);

        return 1;
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function loadJsonData(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new InvalidArgumentException("Can not read file \"{$filePath}\"");
        }

        $data = json_decode(file_get_contents($filePath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Can not parse JSON data: ' . json_last_error_msg());
        }

        return $data;
    }
}