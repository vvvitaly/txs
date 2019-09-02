<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\CompositeMessageParser;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Composite parser for all Sber parsers
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