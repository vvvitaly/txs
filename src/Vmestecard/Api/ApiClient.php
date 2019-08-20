<?php

declare(strict_types=1);

namespace App\Vmestecard\Api;

use App\Libs\Date\DateRange;
use Exception;
use Http\Client\Exception as HttpClientException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;

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
     * @param HttpClient $httpClient
     * @param RequestFactory $requestFactory
     */
    public function __construct(HttpClient $httpClient, RequestFactory $requestFactory = null)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @inheritDoc
     */
    public function getHistory(DateRange $dateRange, Pagination $pagination): array
    {
        $httpRequest = $this->requestFactory->createRequest(
            'GET',
            '/api/History?' . http_build_query([
                'filter.count' => $pagination->getLimit(),
                'filter.from' => $pagination->getOffset(),
                'filter.fromDate' => $dateRange->getBegin() ? $dateRange->getBegin()->format('Y-m-d\TH:i:s.000\Z') : null,
                'filter.toDate' => $dateRange->getEnd() ? $dateRange->getEnd()->format('Y-m-d\TH:i:s.999\Z') : null,
            ])
        );

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (HttpClientException $e) {
            throw new ApiErrorException('API call error', 0, $e);
        } catch (Exception $e) {
            throw new ApiErrorException('Can not process the request', 0, $e);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        $status = $data['result']['state'] ?? null;
        if ($status !== 'Success') {
            throw new ApiErrorException('Bad response from API: ' . ($data['result']['message'] ?? $data['message'] ?? '(unknown error)'));
        }

        return $data;
    }
}