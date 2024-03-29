<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters;

use Exception;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Export\BillExporterInterface;
use vvvitaly\txs\Core\Export\Data\TransactionCollection;

/**
 * Export batch of bills.
 */
final class CollectionExporter
{
    /**
     * @var BillExporterInterface
     */
    private $exporter;

    /**
     * @param BillExporterInterface $exporter
     */
    public function __construct(BillExporterInterface $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * Export the collection of bills. If some error occurs during the export, the whole process also finishes with
     * exception `CollectionExportException`
     *
     * @param BillsCollection $bills
     *
     * @return TransactionCollection
     * @throws CollectionExportException
     */
    public function export(BillsCollection $bills): TransactionCollection
    {
        $transactions = [];

        foreach ($bills as $bill) {
            try {
                $transactions[] = $this->exporter->exportBill($bill);
            } catch (Exception $exception) {
                throw new CollectionExportException($bill, 'Can not export bill', 0, $exception);
            }
        }

        return new TransactionCollection(...$transactions);
    }
}