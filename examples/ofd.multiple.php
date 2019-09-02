<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use App\Fdo\Api\CascadeApiClient;
use App\Fdo\Api\Clients\OfdRuClient;
use App\Fdo\Api\Clients\TaxcomClient;
use App\Fdo\Api\FdoQrSource;
use App\Fdo\Api\FdoRequest;
use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/debug.php';

if ($argc < 3) {
    echo "Usage: php {$argv[0]} [QR content] [account]\n";
    exit(1);
}

[, $qr, $defaultAccount] = $argv;

$logger = new Logger('http');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../tmp/ofd.log'));

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

$api = new CascadeApiClient(
    new OfdRuClient($httpClient, $messageFactory),
    new TaxcomClient($httpClient, $messageFactory)
);

$qrs = [
    FdoRequest::fromQr($qr),
];
$source = new FdoQrSource($qrs, $api, $defaultAccount);

foreach ($source->read() as $bill) {
    echo dumpBill($bill) . "\n";
}