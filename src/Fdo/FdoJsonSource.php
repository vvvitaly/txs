<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo;

use DateTimeImmutable;
use Exception;
use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillItem;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Source\BillSourceInterface;
use vvvitaly\txs\Core\Source\SourceReadException;


/**
 * Parse transactions from the reports of bill checker application.
 */
final class FdoJsonSource implements BillSourceInterface
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