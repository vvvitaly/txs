<?php

declare(strict_types=1);

namespace App\GnuCash\Export\Contract;

use App\Bills\Bill;
use App\GnuCash\Export\Data\Transaction;

/**
 * Export parsed bill into transaction DTO
 */
interface BillExporterInterface
{
    /**
     * Export specified bill with items. If bill doesn't have required data, this method throws an `InvalidBillException`
     * exception.
     *
     * @param Bill $bill
     *
     * @return Transaction
     * @throws InvalidBillException
     */
    public function exportBill(Bill $bill): Transaction;
}