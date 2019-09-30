<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;

/**
 * Match sber pin & transfer messages: it compare account name, amount and date of the messages.
 * In some cases pin message can contain partial account name. For example:
 * pin message: "карта списания **** {account}"
 * confirmation: "VISA{account} 08:17 перевод ..."
 */
final class TransferSmsPinMatcher
{
    /**
     * @var int
     */
    private $pinLifetime;

    /**
     * @param int $pinLifetime Maximum time in seconds between receiving PIN message and transfer message
     */
    public function __construct(int $pinLifetime = 600)
    {
        $this->pinLifetime = $pinLifetime;
    }


    /**
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage $pinMessage
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage $confirmationMessage
     *
     * @return bool
     */
    public function __invoke(PinMessage $pinMessage, ConfirmationMessage $confirmationMessage): bool
    {
        $transferTime = $confirmationMessage->receivingDate->getTimestamp();
        $pinTime = $pinMessage->receivingDate->getTimestamp();

        return preg_match("/^[A-Z]*{$pinMessage->account}$/", $confirmationMessage->account) === 1
            && $pinMessage->amount === $confirmationMessage->amount
            && $transferTime >= $pinTime
            && ($transferTime - $pinTime <= $this->pinLifetime);
    }
}