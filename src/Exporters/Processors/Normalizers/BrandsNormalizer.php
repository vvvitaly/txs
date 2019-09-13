<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors\Normalizers;

/**
 * Remove words in quotes or brackets (guess they are brands names)
 */
final class BrandsNormalizer
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

        if (strpos($text, 'ООО') !== false) {
            return $text;
        }

        $patterns = [
            '/(?!ООО\s+)""[^"]+""/iu',
            '/(?!ООО\s+)"[^"]+"/iu',
            "/(?!ООО\s+)''[^']+''/iu",
            "/(?!ООО\s+)'[^']+'/iu",
            "/(?!ООО\s+)\([^\)]+\)/iu",
        ];

        return preg_replace($patterns, '', $text, 1);
    }
}