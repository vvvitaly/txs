<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Sms\Parsers\CompositeMessageParser;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer\SberComplexTransferFactory;

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
     * @param SberComplexTransferFactory $complexTransferFactory
     */
    public function __construct(SberComplexTransferFactory $complexTransferFactory)
    {
        $this->complexTransferFactory = $complexTransferFactory;
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
            new SberRefill()
        );

        return new SberValidationDecorator($parser);
    }
}