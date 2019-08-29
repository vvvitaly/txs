<?php

declare(strict_types=1);

namespace App\Fdo\Api\Clients;

use App\Fdo\Api\ApiClientInterface;
use App\Fdo\Api\ApiErrorException;
use App\Fdo\Api\FdoCheque;
use App\Fdo\Api\FdoRequest;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Get cheque from ofd.ru
 */
final class OfdRuClient implements ApiClientInterface
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
    public function __construct(HttpClient $httpClient, RequestFactory $requestFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @inheritDoc
     */
    public function getCheque(FdoRequest $request): ?FdoCheque
    {
        $uri = sprintf(
            'https://check.ofd.ru/rec/%s/%s/%s',
            $request->fiscalDriveNumber,
            $request->fiscalDocumentNumber,
            $request->fiscalSign
        );

        $httpRequest = $this->requestFactory->createRequest('GET', $uri);

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $e) {
            throw new ApiErrorException('Can not perform request to API', 0, $e);
        }

        if ($response->getStatusCode() === 404) {
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            throw new ApiErrorException('API call error', 0, HttpException::create($httpRequest, $response));
        }

        $parser = new OfdRuParser();

        try {
            return $parser->parse($response);
        } catch (ResponseParseException $exception) {
            throw new ApiErrorException('Can not parse response', 0, $exception);
        }
    }
}