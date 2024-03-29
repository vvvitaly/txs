<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use Http\Discovery\MessageFactoryDiscovery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Fdo\Api\ApiClientInterface;
use vvvitaly\txs\Fdo\Api\CascadeApiClient;
use vvvitaly\txs\Fdo\Api\Clients\OfdRuClient;
use vvvitaly\txs\Fdo\Api\Clients\TaxcomClient;

/**
 * Export bills from FDO providers by QR codes (decoded content).
 */
final class FdoApiCommand extends Command
{
    use ExportTrait, FdoQrSourceReadingTrait, HttpClientTrait;

    /**
     * @param BillExporterInterface $billExporter
     * @param LoggerInterface|null $logger
     */
    public function __construct(BillExporterInterface $billExporter, ?LoggerInterface $logger = null)
    {
        parent::__construct();

        $this->setBillsExporter($billExporter);

        if ($logger) {
            $this->setHttpLogger($logger);
        }
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
            ])
            ->setHelp(
                <<<EOS
The <info>fdo</info> command obtains bills from some FDO providers. It takes a <comment>list if QR codes</comment> (prints on every bill) or
<comment>file with list of QR codes</comment> and tries to find it in every supported provider.
EOS
            );

        $this->configureCsvOptions($this->getDefinition());
        $this->configureFdoQrOptions($this->getDefinition());
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $api = $this->buildApiClient();
        $this->exportFdoQrSource($api, $input, $output);

        return 1;
    }

    /**
     * @return ApiClientInterface
     */
    private function buildApiClient(): ApiClientInterface
    {
        $httpClient = $this->buildHttpClient();
        $messageFactory = MessageFactoryDiscovery::find();

        $apis = [
            new OfdRuClient($httpClient, $messageFactory),
            new TaxcomClient($httpClient, $messageFactory),
        ];

        return new CascadeApiClient(...$apis);
    }
}