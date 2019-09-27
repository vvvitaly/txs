<?php

declare(strict_types=1);


namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use vvvitaly\txs\Sms\Message;

/**
 * Storage for complex parser
 */
interface MessagesStorageInterface
{
    /**
     * Find correspondent pin message for the given confirmation one. Returns null if message could not be found.
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage $message
     *
     * @return Message|null
     */
    public function findPinMessage(ConfirmationMessage $message): ?PinMessage;

    /**
     * Keep message with PIN.
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage $message
     *
     * @return void
     */
    public function savePinMessage(PinMessage $message): void;
}