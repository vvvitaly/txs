<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillsCollection;

/**
 * Pretty print a bill
 */
final class BillsPrinterHelper extends Helper
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'bills_printer';
    }

    /**
     * Prints bill as a table.
     *
     * @param BillsCollection $bills
     * @param OutputInterface $output
     */
    public function printBills(BillsCollection $bills, OutputInterface $output): void
    {
        $table = $this->createTable($output);

        $bills->rewind();
        while ($bills->valid()) {
            $bill = $bills->current();

            $bills->next();
            $isLast = !$bills->valid();

            $this->addBillRows($table, $bill, !$isLast);
            if ($isLast) {
                break;
            }
        }

        $table->render();
    }

    /**
     * Print one bill
     *
     * @param OutputInterface $output
     * @param Bill $bill
     */
    public function printBill(Bill $bill, OutputInterface $output): void
    {
        $table = $this->createTable($output);
        $this->addBillRows($table, $bill);

        $table->render();
    }

    /**
     * @param OutputInterface $output
     *
     * @return Table
     */
    private function createTable(OutputInterface $output): Table
    {
        $table = new Table($output);
        $table->setHeaders(['#', 'Date', 'Description', 'Amount', 'Account']);

        return $table;
    }

    /**
     * @param Table $table
     * @param Bill $bill
     * @param bool $separate add separator after bill
     */
    private function addBillRows(Table $table, Bill $bill, bool $separate = false): void
    {
        $table->addRow([
            $bill->getInfo()->getNumber(),
            $bill->getInfo()->getDate()->format('Y-m-d'),
            $bill->getInfo()->getDescription(),
            $bill->getAmount()->getValue() . ' ' . $bill->getAmount()->getCurrency(),
            $bill->getAccount(),
        ]);

        $items = $bill->getItems();
        if ($items) {
            $table->addRow([
                '',
                '',
                new TableSeparator(['colspan' => 3]),
            ]);
        }

        foreach ($items as $billItem) {
            $table->addRow([
                '',
                '',
                $billItem->getDescription(),
                $billItem->getAmount()->getValue(),
                '',
            ]);
        }

        if ($separate) {
            $table->addRow(new TableSeparator());
        }
    }
}