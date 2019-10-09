<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Sms\Parsers\CompositeMessageParser;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\SberComplexTransferFactory;
use vvvitaly\txs\Sms\Parsers\Sber\SberOrder\SberOrderParserFactory;

/**
 * Composite parser for all other parsers
 */
final class SberParserFactory
{
    /**
     * @var SberComplexTransferFactory
     */
    private $complexTransferFactory;

    /**
     * @var SberOrderParserFactory
     */
    private $orderParserFactory;

    public function __construct()
    {
        $this->complexTransferFactory = new SberComplexTransferFactory();
        $this->orderParserFactory = new SberOrderParserFactory();
    }


    /**
     * @return MessageParserInterface
     */
    public function getParser(): MessageParserInterface
    {
        $parser = new CompositeMessageParser(
            new SberPayment(),
            new SberPurchase(),
            new SberTransfer(),
            $this->complexTransferFactory->getParser(),
            new SberWithdrawal(),
            new SberRefill(),
            $this->orderParserFactory->getParser()
        );

        return new SberValidationDecorator($parser);
    }
}