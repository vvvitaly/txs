<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Regex\MatcherInterface;
use vvvitaly\txs\Sms\Parsers\Regex\RegexParser;

/**
 * Parse sber SMS with regular expressions
 */
trait SberRegexParserTrait
{
    /**
     * @var RegexParser
     */
    private $parser;

    /**
     * @var callable
     */
    private $billsFactory;

    /**
     * @var MatcherInterface
     */
    private $regexMatcher;

    /**
     * @param Message $sms
     *
     * @return Bill|null
     * @see \vvvitaly\txs\Sms\Parsers\MessageParserInterface::parse()
     */
    public function parse(Message $sms): ?Bill
    {
        return $this->getRegexParser()->parse($sms);
    }

    /**
     * @return RegexParser
     */
    private function getRegexParser(): RegexParser
    {
        if ($this->parser === null) {
            $this->parser = new RegexParser($this->regexMatcher, $this->billsFactory);
        }

        return $this->parser;
    }

    /**
     * @param MatcherInterface $matcher
     */
    private function setRegularExpression(MatcherInterface $matcher): void
    {
        $this->regexMatcher = $matcher;
    }

    /**
     * @param callable $factory
     */
    private function setBillsFactory(callable $factory): void
    {
        $this->billsFactory = $factory;
    }
}