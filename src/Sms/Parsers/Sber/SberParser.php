<?php

declare(strict_types=1);

namespace App\Sms\Parsers\Sber;

use App\Core\Bills\Bill;
use App\Sms\Message;
use App\Sms\Parsers\CompositeMessageParser;
use App\Sms\Parsers\MessageParserInterface;

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