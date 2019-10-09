<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Regex;

/**
 * Decorator allows to match list of regular expressions. Is stops on the first successful match.
 */
final class RegexList implements MatcherInterface
{
    /**
     * @var MatcherInterface[]
     */
    private $matchers;

    /**
     * @param MatcherInterface ...$matchers
     */
    public function __construct(MatcherInterface ...$matchers)
    {
        $this->matchers = $matchers;
    }

    /**
     * @inheritDoc
     */
    public function match(string $text): ?array
    {
        foreach ($this->matchers as $matcher) {
            if (($matches = $matcher->match($text)) !== null) {
                return $matches;
            }
        }

        return null;
    }
}