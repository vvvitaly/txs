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
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Vmestecard\Api\AccessToken\ApiCredentials;
use vvvitaly\txs\Vmestecard\Api\AccessToken\ApiTokenProvider;
use vvvitaly\txs\Vmestecard\Api\AccessToken\CachedTokenProvider;
use vvvitaly\txs\Vmestecard\Api\Client\ApiClient;
use vvvitaly\txs\Vmestecard\VmestecardSource;

/**
 * Export bills from "Vmeste" loyalty program.
 */
final class VmesteCommand extends Command
{
    use ExportTrait;

    /**
     * @var BillExporterInterface
     */
    private $billExporter;

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param BillExporterInterface $billExporter
     * @param LoggerInterface|null $logger
     * @param CacheInterface|null $cache
     */
    public function __construct(
        BillExporterInterface $billExporter,
        ?LoggerInterface $logger,
        ?CacheInterface $cache
    ) {
        parent::__construct();

        $this->billExporter = $billExporter;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $datesHelp = DatesRangeHelper::getHelp();

        $this
            ->setName('vmeste')
            ->setDescription('Export bills from "Vmeste" loyalty program')
            ->setDefinition([
                new InputArgument('dates', InputArgument::REQUIRED, 'Operations history dates range'),
                new InputOption('username', 'u', InputOption::VALUE_REQUIRED, 'Username'),
                new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password'),
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
The <info>vmeste</info> command obtains operations history from "Vmeste" loyalty program for the given dates range.

{$datesHelp}
EOS
            );

        $this->configureCsvOptions($this->getDefinition());
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = $input->getOption('account');
        $datesRange = $this->getHelper('datesRange')->parseDates($input->getArgument('dates'));

        $username = $input->getOption('username');
        $password = $input->getOption('password');

        if (!$username || !$password) {
            throw new InvalidArgumentException('Wrong credentials');
        }

        $credentials = new ApiCredentials($username, $password);

        $plugins = [
            new ContentLengthPlugin(),
        ];
        if ($this->logger) {
            $plugins[] = new LoggerPlugin($this->logger, new FullHttpMessageFormatter());
        }

        $httpClient = new PluginClient(HttpClientDiscovery::find(), $plugins);

        $messageFactory = MessageFactoryDiscovery::find();
        $tokenProvider = new ApiTokenProvider($credentials, $httpClient, $messageFactory);
        if ($this->cache) {
            $tokenProvider = new CachedTokenProvider($tokenProvider, $this->cache,
                "txs.token.{$credentials->username}.");
        }

        $api = new ApiClient($tokenProvider, $httpClient, $messageFactory);
        $source = new VmestecardSource($api, $datesRange, $account);
        $this->export($source, $this->billExporter, $input, $output);

        return 1;
    }
}