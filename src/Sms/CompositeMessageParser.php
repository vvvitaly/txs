<?php

declare(strict_types=1);

namespace App\Sms;

use App\Core\Bills\Bill;

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
    public function parse(Sms $sms): Bill
    {
        foreach ($this->parsers as $parser) {
            try {
                return $parser->parse($sms);
            } catch (UnknownSmsTypeException $exception) {
            }
        }

        throw new UnknownSmsTypeException('Can not parse the SMS with any of parsers.');
    }
}