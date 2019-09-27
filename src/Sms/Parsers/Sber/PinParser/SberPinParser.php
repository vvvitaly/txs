<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberValidationTrait;

/**
 * Inner parser for work with pin/confirmation SMS
 */
final class SberPinParser implements MessageParserInterface
{
    use SberValidationTrait;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface
     */
    private $storage;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface
     */
    private $pinSmsParser;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface
     */
    private $confirmationSmsParser;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\BillsFactoryInterface
     */
    private $billsFactory;

    /**
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinParserFactoryInterface $parserFactory
     */
    public function __construct(PinParserFactoryInterface $parserFactory)
    {
        $this->storage = $parserFactory->getMessagesStorage();
        $this->pinSmsParser = $parserFactory->getPinSmsParser();
        $this->confirmationSmsParser = $parserFactory->getConfirmationSmsParser();
        $this->billsFactory = $parserFactory->getBillsFactory();
    }

    /**
     * Parse the given SMS:
     * - if it's a PIN message, parser saves it in storage,
     * - if it's a confirmation message, parser tries to find correspondent PIN message in the storage,
     * - otherwise, parser skips the message.
     *
     * If the given message is confirmation one and the correspondent PIN could be found, this method creates a bill.
     *
     * @param \vvvitaly\txs\Sms\Message $sms
     *
     * @return Bill|null
     */
    public function parse(Message $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        $pin = $this->pinSmsParser->parseSms($sms);
        if ($pin !== null) {
            $this->storage->savePinMessage($pin);

            return null;
        }

        $confirmation = $this->confirmationSmsParser->parseSms($sms);
        if ($confirmation !== null) {
            $pin = $this->storage->findPinMessage($confirmation);
        }

        if ($pin !== null && $confirmation !== null) {
            return $this->billsFactory->createBill($pin, $confirmation);
        }

        return null;
    }
}