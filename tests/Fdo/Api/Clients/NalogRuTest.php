<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Fdo\Api\Clients;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\TransferException;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use vvvitaly\txs\Fdo\Api\ApiErrorException;
use vvvitaly\txs\Fdo\Api\Clients\NalogRuClient;
use vvvitaly\txs\Fdo\Api\Clients\NalogRuCredentials;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Fdo\Api\FdoRequest;

final class NalogRuTest extends TestCase
{
    /**
     * @var FdoRequest
     */
    private $fdoRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fdoRequest = FdoRequest::fromQr('t=20190811T1139&s=1405.33&fn=9280440300200295&i=14378&fp=3796110719&n=1');
    }

    public function testGetCheque(): void
    {
        $response = new Response(200, [], file_get_contents(__DIR__ . '/nalog.json'));

        $http = new Client();
        $http->addResponse($response);

        $creds = new NalogRuCredentials('user', 'pass');

        $client = new NalogRuClient($creds, $http, MessageFactoryDiscovery::find());

        $cheque = $client->getCheque($this->fdoRequest);

        /** @var RequestInterface $actualRequest */
        $actualRequest = $http->getLastRequest();
        $this->assertEquals('GET', $actualRequest->getMethod());
        $this->assertEquals(
            'http://proverkacheka.nalog.ru:8888/v1/inns/*/kkts/*/fss/9280440300200295/tickets/14378?fiscalSign=3796110719&sendToEmail=no',
            (string)$actualRequest->getUri()
        );
        $this->assertEquals('Basic dXNlcjpwYXNz', $actualRequest->getHeaderLine('Authorization'));
        $this->assertEquals('None', $actualRequest->getHeaderLine('Device-Id'));
        $this->assertEquals('None', $actualRequest->getHeaderLine('Device-OS'));

        $this->assertNotNull($cheque);
        $this->assertEquals(new DateTimeImmutable('2019-08-04 20:28:00'), $cheque->date);
        $this->assertSame(238.40, $cheque->totalAmount);
        $this->assertSame('ООО Ритейл', $cheque->place);
        $this->assertSame('98438', $cheque->number);

        $this->assertCount(2, $cheque->items);
        $this->assertEquals(new FdoChequeItem('XXXXXXXXX', 98.50), $cheque->items[0]);
        $this->assertEquals(new FdoChequeItem('YYYYYYYYYYY', 139.90), $cheque->items[1]);
    }

    public function testGetChequeClientError(): void
    {
        $http = new Client();
        $http->addException(new TransferException('OfdRuClientTest'));

        $client = new NalogRuClient(new NalogRuCredentials('user', 'pass'), $http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeHttpError(): void
    {
        $http = new Client();
        $http->addResponse(new Response(400, [], 'bad request'));

        $client = new NalogRuClient(new NalogRuCredentials('user', 'pass'), $http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }
}