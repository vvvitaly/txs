<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api\Clients;

use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use vvvitaly\txs\Fdo\Api\ApiClientInterface;
use vvvitaly\txs\Fdo\Api\ApiErrorException;
use vvvitaly\txs\Fdo\Api\FdoCheque;
use vvvitaly\txs\Fdo\Api\FdoRequest;

/**
 * Get cheque from Taxcom platform
 */
final class TaxcomClient implements ApiClientInterface
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
            'https://receipt.taxcom.ru/v01/show?fp=%s&s=%.2f',
            $request->fiscalSign,
            $request->amount
        );

        $httpRequest = $this->requestFactory->createRequest('GET', $uri);

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $e) {
            throw new ApiErrorException('Can not perform request to API', 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new ApiErrorException('API call error', 0, HttpException::create($httpRequest, $response));
        }

        try {
            return $this->parse($response);
        } catch (ResponseParseException $e) {
            throw new ApiErrorException('Can not parse response', 0, $e);
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @return FdoCheque|null
     * @throws ResponseParseException
     */
    private function parse(ResponseInterface $response): ?FdoCheque
    {
        $parser = new TaxcomParser();

        return $parser->parse($response);
    }

}