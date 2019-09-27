<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use vvvitaly\txs\Core\Bills\Bill;

/**
 * Bills creation
 */
interface BillsFactoryInterface
{
    /**
     * Create bill instance based on pin and confirmation message.
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage $pinMessage
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage $confirmationMessage
     *
     * @return \vvvitaly\txs\Core\Bills\Bill
     */
    public function createBill(PinMessage $pinMessage, ConfirmationMessage $confirmationMessage): Bill;
}