<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Formatter\FullHttpMessageFormatter;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Fdo\Api\CascadeApiClient;
use vvvitaly\txs\Fdo\Api\Clients\OfdRuClient;
use vvvitaly\txs\Fdo\Api\Clients\TaxcomClient;
use vvvitaly\txs\Fdo\Api\FdoQrSource;
use vvvitaly\txs\Fdo\Api\FdoRequest;

/**
 * Export bills from FDO providers by QR codes (decodec content).
 */
final class FdoApiCommand extends Command
{
    use ExportTrait;

    /**
     * @var BillExporterInterface
     */
    private $billExporter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param BillExporterInterface $billExporter
     * @param LoggerInterface|null $logger
     */
    public function __construct(BillExporterInterface $billExporter, ?LoggerInterface $logger)
    {
        parent::__construct();

        $this->billExporter = $billExporter;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('fdo')
            ->setDescription('Export bills from FDO providers by QR codes contents')
            ->setDefinition([
                new InputOption('qr', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'QR codes'),
                new InputOption('from', 'f', InputOption::VALUE_REQUIRED, 'File with QR codes'),
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
The <info>fdo</info> command obtains bills from some FDO providers. It takes a <comment>list if QR codes</comment> (prints on every bill) or
<comment>file with list of QR codes</comment> and tries to find it in every supported provider.
EOS
            );

        $this->configureCsvOptions($this->getDefinition());
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (($list = $input->getOption('qr')) !== []) {
            $qrs = $list;
        } elseif (($fileName = $input->getOption('from')) !== null) {
            $qrs = $this->loadQrFromFile($fileName);
        } else {
            throw new InvalidArgumentException('One of the option "qr" or "file" must be specified. See --help for information');
        }

        $account = $input->getOption('account');

        $plugins = [
            new ContentLengthPlugin(),
        ];

        if ($this->logger) {
            $plugins[] = new LoggerPlugin($this->logger, new FullHttpMessageFormatter());
        }

        $httpClient = new PluginClient(HttpClientDiscovery::find(), $plugins);
        $messageFactory = MessageFactoryDiscovery::find();

        $api = new CascadeApiClient(
            new OfdRuClient($httpClient, $messageFactory),
            new TaxcomClient($httpClient, $messageFactory)
        );

        $requests = array_map(static function (string $qr) {
            try {
                return FdoRequest::fromQr($qr);
            } catch (InvalidArgumentException $exception) {
                throw new InvalidArgumentException("Can not parse \"$qr\": " . $exception->getMessage(), 0, $exception);
            }
        }, $qrs);

        $source = new FdoQrSource($requests, $api, $account);
        $this->export($source, $this->billExporter, $input, $output);

        $skipped = count($source->getSkippedRequests());
        if ($skipped && $output->isVerbose()) {
            $output->writeln("<info>These requests weren't found:</info>");
            foreach ($source->getSkippedRequests() as $fdoRequest) {
                $output->writeln('  ' . $fdoRequest->asQr());
            }
        } elseif($skipped) {
            $output->writeln("<info>{$skipped}</info> requests weren't found");
        }

        return 1;
    }

    /**
     * Read the file and load QR content from each line.
     *
     * @param string $fileName
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function loadQrFromFile(string $fileName): array
    {
        if (!is_readable($fileName)) {
            throw new InvalidArgumentException("Can not read file \"{$fileName}\"");
        }

        return array_filter(array_map('trim', file($fileName)));
    }
}