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
     * @var \vvvitaly\txs\Sms\Parsers\Regex\RegexParser
     */
    private $parser;

    /**
     * @var callable
     */
    private $billsFactory;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Regex\MatcherInterface
     */
    private $regexMatcher;

    /**
     * @param \vvvitaly\txs\Sms\Parsers\Regex\MatcherInterface $matcher
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

    /**
     * @param \vvvitaly\txs\Sms\Message $sms
     *
     * @return \vvvitaly\txs\Core\Bills\Bill|null
     * @see \vvvitaly\txs\Sms\Parsers\MessageParserInterface::parse()
     */
    public function parse(Message $sms): ?Bill
    {
        return $this->getRegexParser()->parse($sms);
    }

    /**
     * @return \vvvitaly\txs\Sms\Parsers\Regex\RegexParser
     */
    private function getRegexParser(): RegexParser
    {
        if ($this->parser === null) {
            $this->parser = new RegexParser($this->regexMatcher, $this->billsFactory);
        }

        return $this->parser;
    }
}