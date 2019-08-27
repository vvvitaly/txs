<?php

declare(strict_types=1);

namespace App\Ofd;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Core\Bills\BillItem;
use App\Core\Bills\BillsCollection;
use App\Core\Source\BillSourceInterface;
use App\Core\Source\SourceReadException;
use DateTimeImmutable;
use Exception;


/**
 * Parse transactions from the reports of bill checker application.
 */
final class OfdJsonSource implements BillSourceInterface
{
    /**
     * @var string
     */
    private $jsonReportDocument;

    /**
     * @var string
     */
    private $defaultAccount;

    /**
     * @param array $jsonReport
     * @param string $defaultAccount assigned account
     */
    public function __construct(array $jsonReport, string $defaultAccount)
    {
        $this->jsonReportDocument = $jsonReport;
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * @inheritDoc
     */
    public function read(): BillsCollection
    {
        $bills = [];

        foreach ($this->jsonReportDocument as $item) {
            $bills[] = $this->parseReceipt($item['document']['receipt']);
        }

        return new BillsCollection(...$bills);
    }

    /**
     * Parse one receipt from json report receipt (located in "/document/receipt").
     *
     * @param array $response
     *
     * @return Bill
     */
    private function parseReceipt(array $response): Bill
    {
        try {
            $date = new DateTimeImmutable($response['dateTime']);
        } catch (Exception $e) {
            throw new SourceReadException('Can not read receipt date', 0, $e);
        }

        $items = [];
        foreach ($response['items'] as $receiptItem) {
            $items[] = new BillItem($receiptItem['name'], $this->convertAmount($receiptItem['sum']));
        }


        return new Bill(
            $this->convertAmount($response['totalSum']),
            $this->defaultAccount,
            new BillInfo($date, $response['user'] ?? '', (string)$response['fiscalDocumentNumber']),
            $items
        );
    }

    /**
     * Convert response amount to Amount instance.
     *
     * @param int $amount
     *
     * @return Amount
     * @see Amount
     */
    private function convertAmount(int $amount): Amount
    {
        return new Amount((float)$amount / 100);
    }
}