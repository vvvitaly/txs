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

        $patterns = [
            '/""[^"]+""/iu',
            '/"[^"]+"/iu',
            "/''[^']+''/iu",
            "/'[^']+'/iu",
            "/\([^\)]+\)/iu",
        ];

        return preg_replace($patterns, '', $text, 1);
    }
}