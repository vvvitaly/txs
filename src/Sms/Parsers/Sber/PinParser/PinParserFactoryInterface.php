<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

/**
 * Abstract factory for pin parser classes
 */
interface PinParserFactoryInterface
{
    /**
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface
     */
    public function getMessagesStorage(): MessagesStorageInterface;

    /**
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface
     */
    public function getPinSmsParser(): PinSmsParserInterface;

    /**
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface
     */
    public function getConfirmationSmsParser(): ConfirmationSmsParserInterface;

    /**
     * @return \vvvitaly\txs\Sms\Parsers\Sber\PinParser\BillsFactoryInterface
     */
    public function getBillsFactory(): BillsFactoryInterface;
}