<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

/**
 * Parsing text message with list of regular expressions until the first match
 */
trait RegexParsingTrait
{
    /**
     * Run matching list of regular expressions and message text. If match found this method returns array of matches.
     * If no matches found it returns null.
     *
     * @param array $regexList
     *
     * @param string $text
     *
     * @return array
     */
    private function match(array $regexList, string $text): ?array
    {
        foreach ($regexList as $regex) {
            if (preg_match($regex, $text, $matches, PREG_UNMATCHED_AS_NULL)) {
                return $matches;
            }
        }

        return null;
    }
}