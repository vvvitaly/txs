<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors\Normalizers;

/**
 * Remove words without letters
 */
final class WordsNormalizer
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

        return preg_replace('/(?:^|\s)[\W\d]{2,}(\s|$)/iu', ' ', $text);
    }
}