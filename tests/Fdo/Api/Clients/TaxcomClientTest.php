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
use vvvitaly\txs\Fdo\Api\Clients\TaxcomClient;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Fdo\Api\FdoRequest;

final class TaxcomClientTest extends TestCase
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
        $response = new Response(200, [], file_get_contents(__DIR__ . '/taxcom.html'));

        $http = new Client();
        $http->addResponse($response);

        $client = new TaxcomClient($http, MessageFactoryDiscovery::find());

        $cheque = $client->getCheque($this->fdoRequest);

        /** @var RequestInterface $actualRequest */
        $actualRequest = $http->getLastRequest();
        $this->assertEquals('GET', $actualRequest->getMethod());
        $this->assertEquals(
            'https://receipt.taxcom.ru/v01/show?fp=3796110719&s=1405.33',
            (string)$actualRequest->getUri()
        );

        $this->assertNotNull($cheque);
        $this->assertEquals(new DateTimeImmutable('2019-08-11 11:39:00'), $cheque->date);
        $this->assertSame(1405.0, $cheque->totalAmount);
        $this->assertSame('SOME', $cheque->place);
        $this->assertSame('50', $cheque->number);

        $this->assertCount(3, $cheque->items);
        $this->assertEquals(new FdoChequeItem('Кроссовки EKIDEN ONE', 899), $cheque->items[0]);
        $this->assertEquals(new FdoChequeItem('RS 160 MID X3 BLACK', 269), $cheque->items[1]);
        $this->assertEquals(new FdoChequeItem('ARTENGO RS 730', 237), $cheque->items[2]);
    }

    public function testGetChequeParseWithComplexItems(): void
    {
        $response = new Response(200, [], file_get_contents(__DIR__ . '/taxcom2.html'));

        $http = new Client();
        $http->addResponse($response);

        $client = new TaxcomClient($http, MessageFactoryDiscovery::find());

        $cheque = $client->getCheque($this->fdoRequest);

        $this->assertNotNull($cheque);
        $this->assertEquals(new DateTimeImmutable('2019-08-03 08:51:00'), $cheque->date);
        $this->assertSame(1712.45, $cheque->totalAmount);
        $this->assertSame('АЗС N1', $cheque->place);
        $this->assertSame('36', $cheque->number);

        $this->assertCount(1, $cheque->items);
        $this->assertEquals(new FdoChequeItem('Пост 2: АИ95(Кл-5)', 1712.45), $cheque->items[0]);
    }

    public function testGetChequeWithInvalidContent(): void
    {
        $response = new Response(200, [], file_get_contents(__DIR__ . '/taxcom.invalid.html'));

        $http = new Client();
        $http->addResponse($response);

        $client = new TaxcomClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeClientError(): void
    {
        $http = new Client();
        $http->addException(new TransferException('OfdRuClientTest'));

        $client = new TaxcomClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeHttpError(): void
    {
        $http = new Client();
        $http->addResponse(new Response(400, [], 'bad request'));

        $client = new TaxcomClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeWhenNotFound(): void
    {
        $http = new Client();
        $http->addResponse(new Response(200, [], file_get_contents(__DIR__ . '/taxcom.notfound.html')));

        $client = new TaxcomClient($http, MessageFactoryDiscovery::find());

        $cheque = $client->getCheque($this->fdoRequest);

        $this->assertNull($cheque);
    }
}