<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Decorator for sber parsers. It checks message address (900). If address is not correct, returns null.
 */
final class SberValidationDecorator implements MessageParserInterface
{
    /**
     * @var MessageParserInterface
     */
    private $parser;

    /**
     * @param \vvvitaly\txs\Sms\Parsers\MessageParserInterface $parser
     */
    public function __construct(MessageParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if ($sms->from === '900') {
            return $this->parser->parse($sms);
        }

        return null;
    }
}