<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\BillsFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\CanNotCreateBillException;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;

/**
 * Create bill by pin and confirmation message. It takes account and date from confirmation message, and other data -
 * from the pin message.
 */
final class BillsFactory implements BillsFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createBill(OperationStepInterface ...$steps): Bill
    {
        if (count($steps) !== 2) {
            throw new CanNotCreateBillException('Transfer operation must contain only two steps');
        }

        $pin = null;
        $confirmation = null;
        foreach ($steps as $step) {
            if ($step instanceof PinMessageStep) {
                $pin = $step;
            } elseif ($step instanceof ConfirmationMessageStep) {
                $confirmation = $step;
            }
        }

        if ($pin === null) {
            throw new CanNotCreateBillException('PIN step is missing');
        }

        if ($confirmation === null) {
            throw new CanNotCreateBillException('Confirmation step is missing');
        }

        return Composer::expenseBill()
            ->setAmount($pin->getParsedMatches()->amount)
            ->setCurrency($pin->getParsedMatches()->currency)
            ->setAccount($confirmation->getParsedMatches()->account)
            ->setDescription($pin->getParsedMatches()->description)
            ->setDate($confirmation->getParsedMatches()->confirmationDate)
            ->getBill();
    }
}