<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Source;

use vvvitaly\txs\Core\Bills\BillsCollection;

/**
 * Some way for obtaining bills (e.g. SMS, some API, etc.)
 */
interface BillSourceInterface
{
    /**
     * Read source and obtain bills collection
     *
     * @return BillsCollection
     * @throws SourceReadException
     */
    public function read(): BillsCollection;
}