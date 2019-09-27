<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use ArrayObject;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\CompositeMessageParser;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\ConfirmationSmsParser;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\PinSmsParser;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\SberComplexTransfer;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ArrayStorage;

/**
 * Composite parser for all other parsers
 */
final class SberParser implements MessageParserInterface
{
    /**
     * @var MessageParserInterface
     */
    private $parser;

    /**
     */
    public function __construct()
    {
        $this->parser = new CompositeMessageParser(
            new SberPayment(),
            new SberPurchase(),
            new SberTransfer(),
            new SberComplexTransfer(
                new ArrayStorage(new ArrayObject()),
                new PinSmsParser(),
                new ConfirmationSmsParser()
            ),
            new SberWithdrawal(),
            new SberRefill()
        );
    }

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        return $this->parser->parse($sms);
    }
}