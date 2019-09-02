<?php

declare(strict_types=1);

namespace vvvitaly\txs\Infrastructure\Console;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
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
        $table = new Table($output);
        $table->setHeaders(['#', 'Date', 'Description', 'Amount', 'Account']);

        foreach ($bills as $bill) {
            $table->addRow([
                $bill->getInfo()->getNumber(),
                $bill->getInfo()->getDate()->format('Y-m-d'),
                $bill->getInfo()->getDescription(),
                $bill->getAmount()->getValue() . ' ' . $bill->getAmount()->getCurrency(),
                $bill->getAccount(),
            ]);

            foreach ($bill->getItems() as $billItem) {
                $table->addRow([
                    '',
                    $billItem->getDescription(),
                    $billItem->getAmount()->getValue(),
                ]);
            }
        }

        $table->render();
    }
}