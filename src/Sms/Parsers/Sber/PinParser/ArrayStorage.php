<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use ArrayObject;

/**
 * Default implementation of storage based on array. It searches PIN message by the given confirmation message using
 * specified matcher function.
 * The matcher function has following signature:
 * ```
 *  function (PinMessage $pinMessage, ConfirmationMessage $confirmationMessage): bool
 * ```
 * It should return TRUE if given confirmation message matches the pin message. If this function does not set
 */
final class ArrayStorage implements MessagesStorageInterface
{
    /**
     * @var \ArrayObject
     */
    private $storage;

    /**
     * @var callable
     */
    private $matcher;

    /**
     * @param \ArrayObject $storage
     * @param callable|null $matcher
     */
    public function __construct(ArrayObject $storage, callable $matcher)
    {
        $this->storage = $storage;
        $this->matcher = $matcher;
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
        $fn = $this->matcher;

        return $fn($pinMessage, $transferMessage);
    }
}