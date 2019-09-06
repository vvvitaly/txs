<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api\Clients;

use DateTimeImmutable;
use Exception;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use vvvitaly\txs\Fdo\Api\ApiClientInterface;
use vvvitaly\txs\Fdo\Api\ApiErrorException;
use vvvitaly\txs\Fdo\Api\FdoCheque;
use vvvitaly\txs\Fdo\Api\FdoChequeItem;
use vvvitaly\txs\Fdo\Api\FdoRequest;

/**
 * Find cheque in nalog.ru provider
 */
final class NalogRuClient implements ApiClientInterface
{
    /**
     * @var NalogRuCredentials
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
     * @param NalogRuCredentials $credentials
     * @param HttpClient $httpClient
     * @param RequestFactory $requestFactory
     */
    public function __construct(NalogRuCredentials $credentials, HttpClient $httpClient, RequestFactory $requestFactory)
    {
        $this->credentials = $credentials;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @inheritDoc
     */
    public function getCheque(FdoRequest $request): ?FdoCheque
    {
        $uri = sprintf(
            'http://proverkacheka.nalog.ru:8888/v1/inns/*/kkts/*/fss/%s/tickets/%s?fiscalSign=%s&sendToEmail=no',
            $request->fiscalDriveNumber,
            $request->fiscalDocumentNumber,
            $request->fiscalSign
        );

        $httpRequest = $this->requestFactory->createRequest('GET', $uri, [
            'Authorization' => $this->credentials->getAuthHeaderLine(),
            'Device-Id' => 'None',
            'Device-OS' => 'None',
        ]);

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
        $document = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ResponseParseException('Can not parse response: ' . json_last_error_msg());
        }

        $receipt = $document['document']['receipt'] ?? null;
        if (!$receipt) {
            throw new ResponseParseException('Unknown response format');
        }

        $cheque = new FdoCheque();

        try {
            $cheque->date = new DateTimeImmutable($document['document']['receipt']['dateTime']);
        } catch (Exception $e) {
            throw new ResponseParseException('Can not read receipt date', 0, $e);
        }

        $cheque->place = $receipt['user'] ?? '';
        $cheque->number = (string)$receipt['fiscalDocumentNumber'];
        $cheque->totalAmount = $this->convertAmount($receipt['totalSum']);

        $items = [];
        foreach ($receipt['items'] as $receiptItem) {
            $items[] = new FdoChequeItem($receiptItem['name'], $this->convertAmount($receiptItem['sum']));
        }

        $cheque->items = $items;

        return $cheque;
    }

    /**
     * Convert response amount to Amount instance.
     *
     * @param int $amount
     *
     * @return float
     */
    private function convertAmount(int $amount): float
    {
        return (float)$amount / 100;
    }
}