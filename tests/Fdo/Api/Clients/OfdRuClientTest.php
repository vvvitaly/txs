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
use vvvitaly\txs\Fdo\Api\Clients\OfdRuClient;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Fdo\Api\FdoRequest;

final class OfdRuClientTest extends TestCase
{
    /**
     * @var FdoRequest
     */
    private $fdoRequest;

    public function testGetCheque(): void
    {
        $response = new Response(200, [], file_get_contents(__DIR__ . '/ofd.ru.html'));

        $http = new Client();
        $http->addResponse($response);

        $client = new OfdRuClient($http, MessageFactoryDiscovery::find());

        $cheque = $client->getCheque($this->fdoRequest);

        /** @var RequestInterface $actualRequest */
        $actualRequest = $http->getLastRequest();
        $this->assertEquals('GET', $actualRequest->getMethod());
        $this->assertEquals(
            'https://check.ofd.ru/rec/9280440300200295/14378/3796110719',
            (string)$actualRequest->getUri()
        );

        $this->assertNotNull($cheque);
        $this->assertEquals(new DateTimeImmutable('2019-08-11 20:12:00'), $cheque->date);
        $this->assertSame(258.5, $cheque->totalAmount);
        $this->assertSame('SOME', $cheque->place);
        $this->assertSame('#120685', $cheque->number);

        $this->assertCount(3, $cheque->items);
        $this->assertEquals(new FdoChequeItem('Item 1, 1', 118), $cheque->items[0]);
        $this->assertEquals(new FdoChequeItem('Item 2 (2)', 98.5), $cheque->items[1]);
        $this->assertEquals(new FdoChequeItem('Item 3, and some text', 42), $cheque->items[2]);
    }

    public function testGetChequeWithInvalidResponse(): void
    {
        $response = new Response(200, [], '');

        $http = new Client();
        $http->addResponse($response);

        $client = new OfdRuClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeWithInvalidContent(): void
    {
        $response = new Response(200, [], file_get_contents(__DIR__ . '/ofd.ru.invalid.html'));

        $http = new Client();
        $http->addResponse($response);

        $client = new OfdRuClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeClientError(): void
    {
        $http = new Client();
        $http->addException(new TransferException('OfdRuClientTest'));

        $client = new OfdRuClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeHttpError(): void
    {
        $http = new Client();
        $http->addResponse(new Response(400, [], 'bad request'));

        $client = new OfdRuClient($http, MessageFactoryDiscovery::find());

        $this->expectException(ApiErrorException::class);
        $client->getCheque($this->fdoRequest);
    }

    public function testGetChequeWhenNotFound(): void
    {
        $http = new Client();
        $http->addResponse(new Response(404, [], ''));

        $client = new OfdRuClient($http, MessageFactoryDiscovery::find());

        $cheque = $client->getCheque($this->fdoRequest);

        $this->assertNull($cheque);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fdoRequest = FdoRequest::fromQr('t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1');
    }
}