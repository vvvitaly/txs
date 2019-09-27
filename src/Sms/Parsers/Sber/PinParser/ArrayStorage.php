<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use ArrayObject;

/**
 * Default implementation of storage based on array. It searches PIN message by account, amount and date from transfer
 * message.
 */
final class ArrayStorage implements MessagesStorageInterface
{
    /**
     * @var \ArrayObject
     */
    private $storage;

    /**
     * @var int Maximum time in seconds between receiving PIN message and transfer message
     */
    private $pinLifetime;

    /**
     * @param \ArrayObject $storage
     * @param int $pinLifetime Maximum time in seconds between receiving PIN message and transfer message
     */
    public function __construct(ArrayObject $storage, int $pinLifetime = 600)
    {
        $this->storage = $storage;
        $this->pinLifetime = $pinLifetime;
    }

    /**
     * @inheritDoc
     */
    public function findPinMessage(ConfirmationMessage $message): ?PinMessage
    {
        /** @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage $pin */
        foreach ($this->storage as $pin) {
            if ($this->isPinMatchesTransfer($pin, $message)) {
                return $pin;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function savePinMessage(PinMessage $message): void
    {
        $this->storage->append($message);
    }

    /**
     * Check if the pin message is corresponds to the given transfer message.
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage $pinMessage
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage $transferMessage
     *
     * @return bool
     */
    private function isPinMatchesTransfer(PinMessage $pinMessage, ConfirmationMessage $transferMessage): bool
    {
        $transferTime = $transferMessage->receivingDate->getTimestamp();
        $pinTime = $pinMessage->receivingDate->getTimestamp();

        return preg_match("/^[A-Z]*{$pinMessage->account}$/", $transferMessage->account) === 1
            && $pinMessage->amount === $transferMessage->amount
            && $transferTime >= $pinTime
            && ($transferTime - $pinTime <= $this->pinLifetime);
    }
}