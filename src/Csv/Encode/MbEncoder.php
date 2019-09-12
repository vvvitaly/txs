<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv\Encode;

/**
 * Encode string with mb_convert_encoding function
 */
final class MbEncoder implements CsvEncoderInterface
{
    /**
     * @var string
     */
    private $encoding;

    /**
     * @param string $encoding needed encoding
     */
    public function __construct(string $encoding = 'UTF-8')
    {
        $this->encoding = $encoding;
    }

    /**
     * @inheritDoc
     */
    public function encode(string $originEncoding, string $text): string
    {
        return mb_convert_encoding($text, $this->encoding, $originEncoding);
    }
}