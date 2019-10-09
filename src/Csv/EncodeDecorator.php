<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

use vvvitaly\txs\Csv\Encode\CsvEncoderInterface;
use vvvitaly\txs\Csv\Encode\EncodeException;

/**
 * Convert each text cell in CSV row with encoder. It requires origin encoding name.
 */
final class EncodeDecorator implements CsvReaderInterface
{
    /**
     * @var CsvReaderInterface
     */
    private $reader;

    /**
     * @var CsvEncoderInterface
     */
    private $encoder;

    /**
     * @var string
     */
    private $documentEncoding;

    /**
     * @param CsvReaderInterface $reader
     * @param CsvEncoderInterface $encoder
     * @param string $documentEncoding
     */
    public function __construct(
        CsvReaderInterface $reader,
        CsvEncoderInterface $encoder,
        string $documentEncoding
    ) {
        $this->reader = $reader;
        $this->encoder = $encoder;
        $this->documentEncoding = $documentEncoding;
    }

    /**
     * @inheritDoc
     */
    public function open(): void
    {
        $this->reader->open();
    }

    /**
     * @inheritDoc
     */
    public function readRow(): ?array
    {
        $row = $this->reader->readRow();

        if ($row === null) {
            return null;
        }

        try {
            return $this->encode($row);
        } catch (EncodeException $exception) {
            throw new CsvReadException('Can not convert encoding', 0, $exception);
        }
    }

    /**
     * @param array $row
     *
     * @return array
     * @throws EncodeException
     */
    private function encode(array $row): array
    {
        $encoder = $this->encoder;
        $originEncoding = $this->documentEncoding;

        $map = static function ($value) use ($encoder, $originEncoding) {
            return is_string($value) ? $encoder->encode($originEncoding, $value) : $value;
        };

        return array_map($map, $row);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->reader->close();
    }
}