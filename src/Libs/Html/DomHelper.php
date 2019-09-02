<?php

declare(strict_types=1);

namespace vvvitaly\txs\Libs\Html;

use DOMDocument;

/**
 * Helper for HTML parsing via DOMDocument
 */
final class DomHelper
{
    /**
     * Load given HTML content into DOM document instance.
     *
     * @param string $html
     *
     * @return DOMDocument
     */
    public static function loadDocument(string $html): DOMDocument
    {
        if ($html === '') {
            throw new DOMLoadingException('Can load empty content');
        }

        $isInternalErrorsUsed = libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $isLoaded = $document->loadHTML($html);

        libxml_use_internal_errors($isInternalErrorsUsed);

        if (!$isLoaded) {
            $err = libxml_get_last_error();
            throw new DOMLoadingException('Can not load content: ' . $err->message);
        }

        return $document;
    }

    /**
     * Normalized parsed text value.
     *
     * @param string $text
     *
     * @return string
     */
    public static function normalizeText(string $text): string
    {
        return preg_replace('/^\s+|\s+$/u', '', html_entity_decode($text));
    }
}