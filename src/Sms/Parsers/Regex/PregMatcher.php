<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Regex;

/**
 * Regular expression matcher based on PHP function
 */
final class PregMatcher implements MatcherInterface
{
    private const MATCH_FIRST = 'first';
    private const MATCH_GLOBAL = 'global';

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param string $pattern
     * @param string $mode
     */
    private function __construct(string $pattern, string $mode)
    {
        $this->pattern = $pattern;
        $this->mode = $mode;
    }

    /**
     * Search only first match.
     *
     * @param string $pattern
     *
     * @return static
     */
    public static function matchFirst(string $pattern): self
    {
        return new self($pattern, self::MATCH_FIRST);
    }

    /**
     * Perform global search.
     *
     * @param string $pattern
     *
     * @return static
     */
    public static function matchGlobal(string $pattern): self
    {
        return new self($pattern, self::MATCH_GLOBAL);
    }

    /**
     * @inheritDoc
     */
    public function match(string $text): ?array
    {
        if ($this->mode === self::MATCH_FIRST) {
            $found = preg_match($this->pattern, $text, $matches, PREG_UNMATCHED_AS_NULL);

            return $found === 1 ? $matches : null;
        }

        if ($this->mode === self::MATCH_GLOBAL) {
            $found = preg_match_all($this->pattern, $text, $matches, PREG_UNMATCHED_AS_NULL);

            return (int)$found > 0 ? $matches : null;
        }

        return null;
    }
}