<?php

declare(strict_types=1);

namespace App\Vmestecard\Api;

use App\Libs\Date\DateRange;
use App\Vmestecard\SourceReadErrorException;
use App\Vmestecard\Transaction;
use App\Vmestecard\TransactionItem;
use App\Vmestecard\TransactionsSourceInterface;
use DateTimeImmutable;
use Exception;
use Generator;

/**
 * Read transactions via API
 */
final class ApiSource implements TransactionsSourceInterface
{
    /**
     * @var ApiClientInterface
     */
    private $client;

    /**
     * @var DateRange
     */
    private $dateRange;

    /**
     * @param ApiClientInterface $client
     * @param DateRange $dateRange
     */
    public function __construct(ApiClientInterface $client, DateRange $dateRange)
    {
        $this->client = $client;
        $this->dateRange = $dateRange;
    }

    /**
     * @inheritDoc
     */
    public function read(): Generator
    {
        try {
            $response = $this->client->getHistory($this->dateRange, new Pagination(1000));
        } catch (ApiErrorException $exception) {
            throw new SourceReadErrorException('Can not obtain history via API', 0, $exception);
        }

        foreach ($response['data']['rows'] as $row) {
            try {
                $transaction = $this->parseRow($row);
            } catch (Exception $exception) {
                throw new SourceReadErrorException('Can not read API response', 0, $exception);
            }

            yield $transaction;
        }
    }

    /**
     * @param array $row
     *
     * @return Transaction
     * @throws Exception
     */
    private function parseRow(array $row): Transaction
    {
        $transaction = new Transaction();
        $transaction->date = new DateTimeImmutable($row['dateTime']);
        $transaction->amount = (float)$row['data']['amount']['amount'];
        $transaction->chequeNumber = $row['data']['chequeNumber'];
        foreach ($row['data']['chequeItems'] as $item) {
            $transaction->items[] = new TransactionItem((float)$item['amount'], $item['description']);
        }

        return $transaction;
    }
}