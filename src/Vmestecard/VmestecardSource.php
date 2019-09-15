<?php

declare(strict_types=1);

namespace vvvitaly\txs\Vmestecard;

use DateTimeImmutable;
use Exception;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Core\Source\BillSourceInterface;
use vvvitaly\txs\Core\Source\SourceReadException;
use vvvitaly\txs\Libs\Date\DatesRange;
use vvvitaly\txs\Vmestecard\Api\ApiClientInterface;
use vvvitaly\txs\Vmestecard\Api\ApiErrorException;
use vvvitaly\txs\Vmestecard\Api\Client\Pagination;

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
     * @var DatesRange
     */
    private $dateRange;

    /**
     * @var string
     */
    private $defaultAccount;

    /**
     * @param ApiClientInterface $apiClient
     * @param DatesRange $dateRange
     * @param string $defaultAccount
     */
    public function __construct(ApiClientInterface $apiClient, DatesRange $dateRange, string $defaultAccount)
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
        $composer = Composer::newBill()
            ->setAccount($this->defaultAccount)
            ->setAmount((float)$row['data']['amount']['amount'])
            ->setDate(new DateTimeImmutable($row['dateTime']))
            ->setBillNumber($row['data']['chequeNumber']);

        foreach ($row['data']['chequeItems'] as $chequeItem) {
            $composer->addItem((float)$chequeItem['amount'], $chequeItem['description']);
        }

        return $composer->getBill();
    }
}