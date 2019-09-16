<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo;

use DateTimeImmutable;
use Exception;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Bills\Composer;
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
        $composer = Composer::expenseBill()
            ->setAccount($this->defaultAccount)
            ->setAmount($this->convertAmount($response['totalSum']))
            ->setDescription($response['user'] ?? '')
            ->setBillNumber((string)$response['fiscalDocumentNumber']);

        try {
            $date = new DateTimeImmutable($response['dateTime']);
        } catch (Exception $e) {
            throw new SourceReadException('Can not read receipt date', 0, $e);
        }
        $composer->setDate($date);

        foreach ($response['items'] as $receiptItem) {
            $composer->addItem($this->convertAmount($receiptItem['sum']), $receiptItem['name']);
        }

        return $composer->getBill();
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