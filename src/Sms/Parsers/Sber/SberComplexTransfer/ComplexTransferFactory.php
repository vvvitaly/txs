<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use ArrayObject;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ArrayStorage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\BillsFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinParserFactoryInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface;

/**
 * Factory implementation for sber complex transfers.
 */
final class ComplexTransferFactory implements PinParserFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function getMessagesStorage(): MessagesStorageInterface
    {
        return new ArrayStorage(new ArrayObject(), new TransferSmsPinMatcher());
    }

    /**
     * @inheritDoc
     */
    public function getPinSmsParser(): PinSmsParserInterface
    {
        return new PinSmsParser();
    }

    /**
     * @inheritDoc
     */
    public function getConfirmationSmsParser(): ConfirmationSmsParserInterface
    {
        return new ConfirmationSmsParser();
    }

    /**
     * @inheritDoc
     */
    public function getBillsFactory(): BillsFactoryInterface
    {
        return new BillsFactory();
    }
}