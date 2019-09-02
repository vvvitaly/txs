<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use vvvitaly\txs\Fdo\Api\Clients\OfdRuClient;
use vvvitaly\txs\Fdo\Api\FdoQrSource;
use vvvitaly\txs\Fdo\Api\FdoRequest;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/debug.php';

if ($argc < 3) {
    echo "Usage: php {$argv[0]} [QR content] [account]\n";
    exit(1);
}

[, $qr, $defaultAccount] = $argv;

$logger = new Logger('http');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../tmp/ofd-ru.log'));

$httpClient = new PluginClient(
    HttpClientDiscovery::find(),
    [
        new ContentLengthPlugin(),
        new LoggerPlugin($logger, new FullHttpMessageFormatter()),
        new HeaderDefaultsPlugin([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
        ]),
    ]
);
$messageFactory = MessageFactoryDiscovery::find();

$api = new OfdRuClient($httpClient, $messageFactory);

$qrs = [
    FdoRequest::fromQr($qr),
];
$source = new FdoQrSource($qrs, $api, $defaultAccount);

foreach ($source->read() as $bill) {
    echo dumpBill($bill) . "\n";
}