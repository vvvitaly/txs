<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Psr\Log\LoggerInterface;

/**
 * Using HTTP client in command
 */
trait HttpClientTrait
{
    /**
     * @var LoggerInterface
     */
    private $httpLogger;

    /**
     * @param LoggerInterface $logger
     */
    private function setHttpLogger(LoggerInterface $logger): void
    {
        $this->httpLogger = $logger;
    }

    /**
     * @return HttpClient
     */
    private function buildHttpClient(): HttpClient
    {
        $plugins = [
            new ContentLengthPlugin(),
        ];

        if ($this->httpLogger) {
            $plugins[] = new LoggerPlugin($this->httpLogger, new FullHttpMessageFormatter());
        }

        return new PluginClient(HttpClientDiscovery::find(), $plugins);
    }
}