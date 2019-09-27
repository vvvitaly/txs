<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\BillsFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;

/**
 * Create bill by pin and confirmation message. It takes account and date from confirmation message, and other data -
 * from the pin message.
 */
final class BillsFactory implements BillsFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createBill(PinMessage $pinMessage, ConfirmationMessage $confirmationMessage): Bill
    {
        return Composer::expenseBill()
            ->setAmount($pinMessage->amount)
            ->setCurrency($pinMessage->currency)
            ->setAccount($confirmationMessage->account)
            ->setDescription($pinMessage->description)
            ->setDate($confirmationMessage->operationDate)
            ->getBill();
    }

}