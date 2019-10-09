<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Fdo\Api\ApiClientInterface;
use vvvitaly\txs\Fdo\Api\FdoQrSource;
use vvvitaly\txs\Fdo\Api\FdoRequest;

/**
 * Read FDO source with QR codes list
 */
trait FdoQrSourceReadingTrait
{
    /**
     * @var BillExporterInterface
     */
    private $billsExporter;

    /**
     * @param BillExporterInterface $billsExporter
     */
    public function setBillsExporter(BillExporterInterface $billsExporter): void
    {
        $this->billsExporter = $billsExporter;
    }

    /**
     * Add options related with FDO reading.
     *
     * @param InputDefinition $definition
     */
    private function configureFdoQrOptions(InputDefinition $definition): void
    {
        $definition->addOptions([
            new InputOption('qr', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'QR codes'),
            new InputOption('from', 'f', InputOption::VALUE_REQUIRED, 'File with QR codes'),
            new InputOption(
                'account',
                null,
                InputOption::VALUE_REQUIRED,
                'Default account name for operations',
                '_UNASSIGNED_'
            ),
        ]);
    }

    /**
     * @param InputInterface $input
     *
     * @return FdoRequest[]
     * @throws InvalidArgumentException
     */
    private function createQrRequests(InputInterface $input): array
    {
        if (($list = $input->getOption('qr')) !== []) {
            $qrs = $list;
        } elseif (($fileName = $input->getOption('from')) !== null) {
            $qrs = $this->loadQrFromFile($fileName);
        } else {
            throw new InvalidArgumentException('One of the option "qr" or "file" must be specified. See --help for information');
        }

        return array_map(static function (string $qr) {
            try {
                return FdoRequest::fromQr($qr);
            } catch (InvalidArgumentException $exception) {
                throw new InvalidArgumentException("Can not parse \"$qr\": " . $exception->getMessage(), 0, $exception);
            }
        }, $qrs);
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

    /**
     * Run exporting for the given account
     *
     * @param ApiClientInterface $apiClient
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function exportFdoQrSource(
        ApiClientInterface $apiClient,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $account = $input->getOption('account');
        $requests = $this->createQrRequests($input);

        $source = new FdoQrSource($requests, $apiClient, $account);
        $this->export($source, $this->billsExporter, $input, $output);

        $skipped = count($source->getSkippedRequests());
        if ($skipped && $output->isVeryVerbose()) {
            $output->writeln("<info>These requests weren't found:</info>");
            foreach ($source->getSkippedRequests() as $fdoRequest) {
                $output->writeln('  ' . $fdoRequest->asQr());
            }
        } elseif ($skipped) {
            $output->writeln("<info>{$skipped}</info> requests weren't found");
        }
    }
}