<?php

declare(strict_types=1);

namespace tests\Vmestecard\Api\AccessToken;

use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\TransferException;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use vvvitaly\txs\Vmestecard\Api\AccessToken\ApiCredentials;
use vvvitaly\txs\Vmestecard\Api\AccessToken\ApiTokenProvider;
use vvvitaly\txs\Vmestecard\Api\AccessToken\TokenNotFoundException;

/** @noinspection PhpMissingDocCommentInspection */

final class ApiTokenProviderTest extends TestCase
{
    public function testGetToken(): void
    {
        $response = new Response(200, [],
            '{"access_token":"test.token","token_type":"bearer","expires_in":2591999,"refresh_token":"some"}');
        $creds = new ApiCredentials('user', 'pass');

        $http = new Client();
        $http->addResponse($response);

        $tokenProvider = new ApiTokenProvider($creds, $http, MessageFactoryDiscovery::find());
        $token = $tokenProvider->getToken();

        $this->assertEquals('test.token', $token->getToken());
        $this->assertSame(2591999, $token->getLifetime());

        /** @var RequestInterface $actualRequest */
        $actualRequest = $http->getLastRequest();

        $this->assertEquals('POST', $actualRequest->getMethod());
        $this->assertEquals('/token', $actualRequest->getUri()->getPath());

        $post = [];
        parse_str($actualRequest->getBody()->getContents(), $post);
        $this->assertEquals([
            'grant_type' => 'password',
            'username' => 'user',
            'password' => 'pass',
        ], $post);
    }

    public function testGetTokenWrongResponse(): void
    {
        $response = new Response(400, [], '{"error":"TestError","error_description":"Some error"}');

        $http = new Client();
        $http->addResponse($response);

        $tokenProvider = new ApiTokenProvider(new ApiCredentials('test', 'pwd'), $http,
            MessageFactoryDiscovery::find());

        $this->expectException(TokenNotFoundException::class);
        $this->expectExceptionMessageMatches('/Some error/');
        $tokenProvider->getToken();
    }

    public function testGetTokenHttpError(): void
    {
        $http = new Client();
        $http->addException(new TransferException('testGetTokenHttpError'));

        $tokenProvider = new ApiTokenProvider(new ApiCredentials('test', 'pwd'), $http,
            MessageFactoryDiscovery::find());
        $this->expectException(TokenNotFoundException::class);
        $tokenProvider->getToken();
    }
}