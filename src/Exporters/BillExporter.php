<?php

declare(strict_types=1);

namespace App\Exporters;

use App\Core\Bills\Bill;
use App\Core\Export\Data\Transaction;
use App\Core\Export\Data\TransactionSplit;
use App\Core\Export\InvalidBillException;
use App\Core\Export\BillExporterInterface;
use App\Exporters\Processors\ProcessorInterface;

/**
 * Export the most information from bill to GnuCash-like transactions objects.
 */
final class BillExporter implements BillExporterInterface
{
    private $processorsChain;

    /**
     * @param ProcessorInterface|null $processor
     */
    public function __construct(?ProcessorInterface $processor = null)
    {
        $this->processorsChain = $processor;
    }

    /**
     * @inheritDoc
     */
    public function exportBill(Bill $bill): Transaction
    {
        $this->validateBill($bill);

        $billInfo = $bill->getInfo();
        $amount = $bill->getAmount();

        $transaction = new Transaction();
        $transaction->date = $billInfo->getDate();
        $transaction->num = $billInfo->getNumber();
        $transaction->account = $bill->getAccount();
        $transaction->description = $billInfo->getDescription();
        $transaction->amount = $amount->getValue();
        $transaction->currency = $amount->getCurrency();

        $this->splitTransaction($transaction, $bill->getItems());

        if ($this->processorsChain) {
            $this->processorsChain->process($transaction);
        }

        return $transaction;
    }

    /**
     * If the bill has items, then split transaction by this items. Otherwise add inverse transaction as split.
     *
     * @param Transaction $transaction
     * @param array $billItems
     */
    private function splitTransaction(Transaction $transaction, array $billItems): void
    {
        if (!$billItems) {
            $split = new TransactionSplit();
            $split->amount = $transaction->amount * -1;
            $transaction->splits[] = $split;

            return;
        }

        foreach ($billItems as $billItem) {
            $split = new TransactionSplit();
            $split->amount = $billItem->getAmount()->getValue();
            $split->memo = $billItem->getDescription();
            $transaction->splits[] = $split;
        }
    }

    /**
     * Check mandatory fields: date, account and description.
     *
     * @param Bill $bill
     */
    private function validateBill(Bill $bill): void
    {
        if (!$bill->getInfo()->getDate()) {
            throw new InvalidBillException('Date is mandatory');
        }

        if (!$bill->getAccount()) {
            throw new InvalidBillException('Account is mandatory');
        }

        if (!$bill->getInfo()->getDescription()) {
            throw new InvalidBillException('Transaction description is mandatory');
        }
    }
}