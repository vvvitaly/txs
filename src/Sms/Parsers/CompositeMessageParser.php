<?php

declare(strict_types=1);

namespace App\Sms\Parsers;

use App\Core\Bills\Bill;
use App\Sms\Message;

/**
 * Try to parse SMS with list of parsers. If one of the parsers throws UnknownSmsTypeException, it tries parsing with
 * the next one. If no parser could process the SMS the UnknownSmsTypeException will thrown.
 */
final class CompositeMessageParser implements MessageParserInterface
{
    /**
     * @var MessageParserInterface[]
     */
    private $parsers;

    /**
     * @param MessageParserInterface[] $parser
     */
    public function __construct(MessageParserInterface ...$parser)
    {
        $this->parsers = $parser;
    }

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        foreach ($this->parsers as $parser) {
            if (($bill = $parser->parse($sms)) !== null) {
                return $bill;
            }
        }

        return null;
    }
}