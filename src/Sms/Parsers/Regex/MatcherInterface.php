<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Regex;

/**
 * Regular expressions matcher
 */
interface MatcherInterface
{
    /**
     * Search in the text by regular expression and return found matches or null, if the text is not matched.
     *
     * @param string $text
     *
     * @return array|null
     */
    public function match(string $text): ?array;
}