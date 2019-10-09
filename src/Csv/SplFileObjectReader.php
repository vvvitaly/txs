<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

use SplFileObject;

/**
 * Read CSV with SplFileObject instance
 *
 * @see \SplFileObject
 */
final class SplFileObjectReader implements CsvReaderInterface
{
    /**
     * @var SplFileObject
     */
    private $file;

    /**
     * @var CsvControl
     */
    private $csvControl;

    /**
     * @var array
     */
    private $prevCsvControl;

    /**
     * @var int
     */
    private $prevFlags;

    /**
     * @param SplFileObject $file
     * @param CsvControl $csvControl
     */
    public function __construct(SplFileObject $file, CsvControl $csvControl)
    {
        $this->file = $file;
        $this->csvControl = $csvControl;
    }

    /**
     * @inheritDoc
     */
    public function open(): void
    {
        $this->prevCsvControl = $this->file->getCsvControl();
        $this->prevFlags = $this->file->getFlags();

        $this->file->setCsvControl(
            $this->csvControl->csvSeparator,
            $this->csvControl->enclosure,
            $this->csvControl->escape
        );
        $this->file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
    }

    /**
     * @inheritDoc
     */
    public function readRow(): ?array
    {
        if ($this->file->eof()) {
            return null;
        }

        return $this->file->fgetcsv(
            $this->csvControl->csvSeparator,
            $this->csvControl->enclosure,
            $this->csvControl->escape
        );
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->prevCsvControl) {
            $this->file->setCsvControl(...$this->prevCsvControl);
            $this->prevCsvControl = null;
        }

        if ($this->prevFlags) {
            $this->file->setFlags($this->prevFlags);
            $this->prevFlags = null;
        }
    }
}