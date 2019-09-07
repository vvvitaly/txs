<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors\Normalizers;

/**
 * Remove contractions and abbreviations following the rules:
 *  - all contractions consisting of two letters separated with "/" (x/y)
 *  - special contractions: "п/сух", "н/газ"
 *  - word "дет."
 */
final class ContractionsNormalizer
{
    /**
     * @param string|null $text
     *
     * @return string|null
     */
    public function __invoke(?string $text): ?string
    {
        if (!$text) {
            return $text;
        }

        $patterns = [
            '#\b[а-яa-z]/[а-яa-z]\b#iu',
            '#(п/сух)|(н/газ)#iu',
            '/\b(дет)[\b.]/iu',
        ];

        return preg_replace($patterns, '', $text);
    }
}