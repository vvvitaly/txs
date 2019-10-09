<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Regex;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Abstract SMS parser based on simple regular expression. It parses message text by regular expression and creates
 * a bill via factory function. This factory has signature:
 *  ```
 *      function (\vvvitaly\txs\Sms\Message $sms, array $matches): ?Bill;
 *  ```
 */
final class RegexParser implements MessageParserInterface
{
    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var callable
     */
    private $billsFactory;

    /**
     * @param MatcherInterface $matcher
     * @param callable $billsFactory
     */
    public function __construct(MatcherInterface $matcher, callable $billsFactory)
    {
        $this->matcher = $matcher;
        $this->billsFactory = $billsFactory;
    }

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if (($matches = $this->matcher->match($sms->text)) !== null) {
            return $this->createBill($sms, $matches);
        }

        return null;
    }

    /**
     * @param Message $sms
     * @param array $matches
     *
     * @return Bill
     */
    private function createBill(Message $sms, array $matches): ?Bill
    {
        $factory = $this->billsFactory;

        return $factory($sms, $matches);
    }
}