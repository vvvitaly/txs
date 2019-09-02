<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use App\Libs\Date\DateRange;
use App\Vmestecard\Api\AccessToken\ApiCredentials;
use App\Vmestecard\Api\AccessToken\ApiTokenProvider;
use App\Vmestecard\Api\AccessToken\CachedTokenProvider;
use App\Vmestecard\Api\Client\ApiClient;
use App\Vmestecard\VmestecardSource;
use Desarrolla2\Cache\File;
use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/debug.php';

if ($argc < 5) {
    echo "Usage: php {$argv[0]} [username] [password] [date from] [account]\n";
    exit(1);
}

[, $username, $password, $dateFrom, $defaultAccount] = $argv;

// API credential
$credentials = new ApiCredentials($username, $password);

// Source filter
$dateRange = new DateRange(new DateTimeImmutable($dateFrom));

// create HTTP client
$logger = new Logger('http');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../tmp/vc.log'));

$httpClient = new PluginClient(
    HttpClientDiscovery::find(),
    [
        new AddHostPlugin(UriFactoryDiscovery::find()->createUri('https://api-zhuravli.vmeste32.productions')),
        new ContentLengthPlugin(),
        new LoggerPlugin($logger, new FullHttpMessageFormatter()),
    ]
);
$messageFactory = MessageFactoryDiscovery::find();

$tokenProvider = new CachedTokenProvider(
    new ApiTokenProvider($credentials, $httpClient, $messageFactory),
    new File(__DIR__ . '/../tmp'),
    'txs.' . $credentials->username . '.'
);

$api = new ApiClient($tokenProvider, $httpClient, $messageFactory);

$source = new VmestecardSource($api, $dateRange, $defaultAccount);

foreach ($source->read() as $bill) {
    echo dumpBill($bill) . "\n";
}