<?php

declare(strict_types=1);

namespace vvvitaly\txs\Vmestecard\Api\AccessToken;

use Exception;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Obtain access via API "/token"
 */
final class ApiTokenProvider implements TokenProviderInterface
{
    /**
     * @var ApiCredentials
     */
    private $credentials;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @param ApiCredentials $credentials
     * @param HttpClient $httpClient
     * @param RequestFactory $requestFactory
     */
    public function __construct(ApiCredentials $credentials, HttpClient $httpClient, RequestFactory $requestFactory)
    {
        $this->credentials = $credentials;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @inheritDoc
     */
    public function getToken(): ApiToken
    {
        $httpRequest = $this->requestFactory->createRequest(
            'POST',
            '/token',
            [],
            http_build_query([
                'grant_type' => 'password',
                'username' => $this->credentials->username,
                'password' => $this->credentials->password,
            ])
        );

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $e) {
            throw new TokenNotFoundException('API call error', 0, $e);
        } catch (Exception $e) {
            throw new TokenNotFoundException('Can not process the request', 0, $e);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200) {
            $message = $data['error_description'] ?? '(unknown error)';

            throw new TokenNotFoundException(
                "API error: \"$message\"",
                0,
                HttpException::create($httpRequest, $response)
            );
        }

        return ApiToken::fromResponse($data);
    }
}