<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\BillsFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\CanNotCreateBillException;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;

/**
 * Create bill by pin and confirmation message. It takes account from the PIN message, and other data -
 * from the confirmation message. It uses confirmation receiving date as bill date.
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
            ->setAmount($confirmation->getParsedMatches()->amount)
            ->setCurrency($confirmation->getParsedMatches()->currency)
            ->setAccount($pin->getParsedMatches()->account)
            ->setDescription('Заказ в магазине ' . $confirmation->getParsedMatches()->store)
            ->setDate($confirmation->getOriginSms()->date)
            ->getBill();
    }
}