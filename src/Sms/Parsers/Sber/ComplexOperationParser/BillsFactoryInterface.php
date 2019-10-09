<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser;

use vvvitaly\txs\Core\Bills\Bill;

/**
 * Bills creation
 */
interface BillsFactoryInterface
{
    /**
     * Create bill instance based on parsed steps from SMS related to one operation.
     *
     * @param OperationStepInterface[] $steps
     *
     * @return Bill
     * @throws CanNotCreateBillException
     */
    public function createBill(OperationStepInterface ...$steps): Bill;
}