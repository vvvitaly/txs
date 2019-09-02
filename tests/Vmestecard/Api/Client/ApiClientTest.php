<?php

declare(strict_types=1);

namespace tests\Vmestecard\Api\Client;

use App\Libs\Date\DatesRange;
use App\Vmestecard\Api\AccessToken\ApiToken;
use App\Vmestecard\Api\AccessToken\TokenProviderInterface;
use App\Vmestecard\Api\ApiErrorException;
use App\Vmestecard\Api\Client\ApiClient;
use App\Vmestecard\Api\Client\Pagination;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\TransferException;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/** @noinspection PhpMissingDocCommentInspection */

final class ApiClientTest extends TestCase
{
    public function testGetHistory(): void
    {
        $dates = new DatesRange(new DateTimeImmutable('2019-08-01 12:33:44'),
            new DateTimeImmutable('2019-08-12 23:00:12'));
        $pagination = new Pagination(1000);

        $response = new Response(200, [], self::successfulResponse());

        $http = new Client();
        $http->addResponse($response);

        $tokenProvider = $this->createMock(TokenProviderInterface::class);
        $tokenProvider->expects($this->once())
            ->method('getToken')
            ->willReturn(new ApiToken('test.token', 3600));

        $client = new ApiClient($tokenProvider, $http, MessageFactoryDiscovery::find());
        $response = $client->getHistory($dates, $pagination);
        $this->assertEquals(json_decode(self::successfulResponse(), true), $response);

        /** @var RequestInterface $actualRequest */
        $actualRequest = $http->getLastRequest();
        $this->assertEquals('GET', $actualRequest->getMethod());
        $this->assertEquals('/api/History', $actualRequest->getUri()->getPath());
        $this->assertEquals('Bearer test.token', $actualRequest->getHeaderLine('Authorization'));

        $query = [];
        parse_str($actualRequest->getUri()->getQuery(), $query);
        $this->assertEquals([ // parse_str replaces '.' to '_'
            'filter_count' => '1000',
            'filter_from' => '0',
            'filter_fromDate' => '2019-08-01T12:33:44.000Z',
            'filter_toDate' => '2019-08-12T23:00:12.999Z',
        ], $query);
    }

    public function testGetHistoryWrongResponse(): void
    {
        $dates = new DatesRange(null, new DateTimeImmutable('now'));
        $pagination = new Pagination(1000);

        $response = new Response(200, [], self::errorResponse('testGetHistoryWrongResponse'));

        $http = new Client();
        $http->addResponse($response);

        $client = new ApiClient(
            $this->createConfiguredMock(TokenProviderInterface::class, ['getToken' => new ApiToken('test', 1)]),
            $http,
            MessageFactoryDiscovery::find()
        );
        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessageMatches('/testGetHistoryWrongResponse/');
        $client->getHistory($dates, $pagination);
    }

    public function testGetHistoryHttpError(): void
    {
        $dates = new DatesRange(null, new DateTimeImmutable('now'));
        $pagination = new Pagination(1000);

        $http = new Client();
        $http->addException(new TransferException('testGetHistoryWrongResponse'));

        $client = new ApiClient(
            $this->createConfiguredMock(TokenProviderInterface::class, ['getToken' => new ApiToken('test', 1)]),
            $http,
            MessageFactoryDiscovery::find()
        );
        $this->expectException(ApiErrorException::class);
        $client->getHistory($dates, $pagination);
    }

    public function testGetHistoryBadStatus(): void
    {
        $dates = new DatesRange(null, new DateTimeImmutable('now'));
        $pagination = new Pagination(1000);

        $response = new Response(403);

        $http = new Client();
        $http->addResponse($response);

        $client = new ApiClient(
            $this->createConfiguredMock(TokenProviderInterface::class, ['getToken' => new ApiToken('test', 1)]),
            $http,
            MessageFactoryDiscovery::find()
        );
        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessageMatches('/http error/i');
        $client->getHistory($dates, $pagination);
    }

    /**
     * @return string
     */
    private static function successfulResponse(): string
    {
        return json_encode([
            'data' => [
                'allCount' => 0,
                'rows' => [],
            ],
            'result' => [
                'state' => 'Success',
                'message' => null,
                'validationErrors' => null,
            ],
        ]);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private static function errorResponse(string $message): string
    {
        return json_encode([
            'data' => [],
            'result' => [
                'state' => 'Error',
                'message' => $message,
                'validationErrors' => null,
            ],
        ]);
    }
}