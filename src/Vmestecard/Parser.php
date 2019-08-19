<?php

declare(strict_types=1);

namespace App\Vmestecard;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Core\Bills\BillItem;
use App\Core\Bills\BillsCollection;

/**
 * Parse data from the Vmestecard account (vmestecard.ru). This parser uses their API to obtain list of purchases by
 * the
 * given dates. As this API does not provide any account information, the default account name is passes by the
 * constructor.
 */
final class Parser
{
    /**
     * @var TransactionsSourceInterface
     */
    private $source;

    /**
     * @var string
     */
    private $defaultAccount;

    /**
     * @param TransactionsSourceInterface $source
     * @param string $defaultAccount
     */
    public function __construct(TransactionsSourceInterface $source, string $defaultAccount)
    {
        $this->source = $source;
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * Parse the given source.
     *
     * @return BillsCollection
     * @throws SourceReadErrorException
     */
    public function parse(): BillsCollection
    {
        $bills = [];
        foreach ($this->source->read() as $transaction) {
            $bills[] = $this->createBill($transaction);
        }

        return new BillsCollection(...$bills);
    }

    /**
     * Create a bill instance.
     *
     * @param Transaction $transaction
     *
     * @return Bill
     */
    private function createBill(Transaction $transaction): Bill
    {
        $items = [];
        foreach ($transaction->items as $chequeItem) {
            $items[] = new BillItem($chequeItem->description, new Amount($chequeItem->amount));
        }

        return new Bill(
            new Amount($transaction->amount),
            $this->defaultAccount,
            new BillInfo($transaction->date, null, $transaction->chequeNumber),
            $items
        );
    }
}