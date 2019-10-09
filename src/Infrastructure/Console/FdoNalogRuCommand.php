<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use Http\Discovery\MessageFactoryDiscovery;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Fdo\Api\ApiClientInterface;
use vvvitaly\txs\Fdo\Api\Clients\NalogRuClient;
use vvvitaly\txs\Fdo\Api\Clients\NalogRuCredentials;

/**
 * Export bills from nalog.ru API by QR codes.
 */
final class FdoNalogRuCommand extends Command
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
            ->setName('fdo.nalogru')
            ->setDescription('Export bills from nalog.ru API by QR codes contents')
            ->setDefinition([
                new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'Username for nalog.ru check'),
                new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password for nalog.ru check'),
            ])
            ->setHelp(
                <<<EOS
The <info>fdo</info> command obtains bills via nalog.ru free API. It takes a <comment>list if QR codes</comment> (prints on every bill) or
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
        $api = $this->buildApiClient($input);
        $this->exportFdoQrSource($api, $input, $output);

        return 1;
    }

    /**
     * @param InputInterface $input
     *
     * @return ApiClientInterface
     */
    private function buildApiClient(InputInterface $input): ApiClientInterface
    {
        $username = $input->getOption('user');
        $password = $input->getOption('password');

        if (!$username || !$password) {
            throw new InvalidArgumentException('Wrong credentials');
        }

        $httpClient = $this->buildHttpClient();
        $messageFactory = MessageFactoryDiscovery::find();

        return new NalogRuClient(new NalogRuCredentials($username, $password), $httpClient, $messageFactory);
    }
}