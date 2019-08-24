<?php

declare(strict_types=1);

namespace App\Vmestecard\Api\Client;

use App\Libs\Date\DateRange;
use App\Vmestecard\Api\AccessToken\TokenProviderInterface;
use App\Vmestecard\Api\ApiClientInterface;
use App\Vmestecard\Api\ApiErrorException;
use DateTimeImmutable;
use Exception;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * API implementation based on HTTP client
 */
final class ApiClient implements ApiClientInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var TokenProviderInterface
     */
    private $tokenProvider;

    /**
     * @param TokenProviderInterface $tokenProvider
     * @param HttpClient $httpClient
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        TokenProviderInterface $tokenProvider,
        HttpClient $httpClient,
        RequestFactory $requestFactory = null
    )
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * @inheritDoc
     */
    public function getHistory(DateRange $dateRange, Pagination $pagination): array
    {
        $token = $this->tokenProvider->getToken();
        if (!$token->isValid()) {
            throw new ApiErrorException('Can not send request because access token is invalid');
        }

        $toDate = $dateRange->getEnd() ?? new DateTimeImmutable('now');
        $httpRequest = $this->requestFactory->createRequest(
            'GET',
            '/api/History?' . http_build_query([
                'filter.count' => $pagination->getLimit(),
                'filter.from' => $pagination->getOffset(),
                'filter.fromDate' => $dateRange->getBegin() ? $dateRange->getBegin()->format('Y-m-d\TH:i:s.000\Z') : null,
                'filter.toDate' => $toDate->format('Y-m-d\TH:i:s.999\Z'),
            ]),
            [
                'Authorization' => 'Bearer ' . $token->getToken(),
            ]
        );

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $e) {
            throw new ApiErrorException('API call error', 0, $e);
        } catch (Exception $e) {
            throw new ApiErrorException('Can not process the request', 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new ApiErrorException('HTTP error during API call', 0,
                HttpException::create($httpRequest, $response));
        }

        $data = json_decode($response->getBody()->getContents(), true);

        $status = $data['result']['state'] ?? null;
        if ($status !== 'Success') {
            throw new ApiErrorException('Bad response from API: ' . ($data['result']['message'] ?? $data['message'] ?? '(unknown error)'));
        }

        return $data;
    }
}