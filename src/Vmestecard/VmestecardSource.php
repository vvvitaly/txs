<?php

declare(strict_types=1);

namespace App\Vmestecard;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Core\Bills\BillItem;
use App\Core\Bills\BillsCollection;
use App\Core\Source\BillSourceInterface;
use App\Core\Source\SourceReadException;
use App\Libs\Date\DateRange;
use App\Vmestecard\Api\ApiClientInterface;
use App\Vmestecard\Api\ApiErrorException;
use App\Vmestecard\Api\Client\Pagination;
use DateTimeImmutable;
use Exception;

/**
 * Parse data from the Vmestecard account (vmestecard.ru). This parser uses their API to obtain list of purchases by
 * the given dates. As this API does not provide any account information, the default account name is passes by the
 * constructor.
 */
final class VmestecardSource implements BillSourceInterface
{
    /**
     * @var ApiClientInterface
     */
    private $apiClient;

    /**
     * @var DateRange
     */
    private $dateRange;

    /**
     * @var string
     */
    private $defaultAccount;

    /**
     * @param ApiClientInterface $apiClient
     * @param DateRange $dateRange
     * @param string $defaultAccount
     */
    public function __construct(ApiClientInterface $apiClient, DateRange $dateRange, string $defaultAccount)
    {
        $this->apiClient = $apiClient;
        $this->dateRange = $dateRange;
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * @inheritDoc
     */
    public function read(): BillsCollection
    {
        try {
            $response = $this->apiClient->getHistory($this->dateRange, new Pagination(1000));
        } catch (ApiErrorException $exception) {
            throw new SourceReadException('Can not obtain history via API', 0, $exception);
        }

        $bills = [];
        foreach ($response['data']['rows'] as $row) {
            if ($row['type'] !== 'PurchaseData') {
                continue;
            }

            try {
                $bills[] = $this->createBill($row);
            } catch (Exception $e) {
                throw new SourceReadException('Can not parse response', 0, $e);
            }
        }

        return new BillsCollection(...$bills);
    }

    /**
     * Create a bill instance by the given response item from the API.
     *
     * @param array $row
     *
     * @return Bill
     * @throws Exception
     */
    private function createBill(array $row): Bill
    {
        $items = [];
        foreach ($row['data']['chequeItems'] as $chequeItem) {
            $items[] = new BillItem($chequeItem['description'], new Amount((float)$chequeItem['amount']));
        }

        return new Bill(
            new Amount((float)$row['data']['amount']['amount']),
            $this->defaultAccount,
            new BillInfo(new DateTimeImmutable($row['dateTime']), null, $row['data']['chequeNumber']),
            $items
        );
    }
}