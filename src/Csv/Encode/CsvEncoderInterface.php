<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv\Encode;

/**
 * Convert string encoding
 */
interface CsvEncoderInterface
{
    /**
     * Convert encoding of the given text in some origin encoding.
     *
     * @param string $originEncoding
     * @param string $text
     *
     * @return string
     * @throws \vvvitaly\txs\Csv\Encode\EncodeException
     */
    public function encode(string $originEncoding, string $text): string;
}