<?php

declare(strict_types=1);


namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;

/**
 * Storage for complex transfer parser
 */
interface MessagesStorageInterface
{
    /**
     * Find correspondent pin message for the given transfer one. Returns null if message could not be found.
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferMessage $message
     *
     * @return Message|null
     */
    public function findPinMessage(TransferMessage $message): ?TransferPinMessage;

    /**
     * Keep message with PIN.
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferPinMessage $message
     *
     * @return void
     */
    public function savePinMessage(TransferPinMessage $message): void;
}