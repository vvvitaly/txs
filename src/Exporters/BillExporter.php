<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Core\Export\Data\TransactionSplit;
use vvvitaly\txs\Core\Export\InvalidBillException;
use vvvitaly\txs\Exporters\Processors\ProcessorInterface;

/**
 * Export the most information from bill to GnuCash-like transactions objects.
 */
final class BillExporter implements BillExporterInterface
{
    private $processor;

    /**
     * @param ProcessorInterface|null $processor
     */
    public function __construct(?ProcessorInterface $processor = null)
    {
        $this->processor = $processor;
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
        $transaction->amount = -1 * $amount->getValue();
        $transaction->currency = $amount->getCurrency();

        $this->splitTransaction($transaction, $bill->getItems());

        if ($this->processor) {
            $this->processor->process($transaction);
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
    }
}