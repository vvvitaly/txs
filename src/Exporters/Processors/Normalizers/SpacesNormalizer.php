<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors\Normalizers;

/**
 * Remove trailing spaces, replace 2 or more space with one
 */
final class SpacesNormalizer
{
    /**
     * @inheritDoc
     */
    public function __invoke(?string $text): ?string
    {
        if (!$text) {
            return $text;
        }

        return trim(preg_replace('/\s{2,}/', ' ', $text));
    }
}