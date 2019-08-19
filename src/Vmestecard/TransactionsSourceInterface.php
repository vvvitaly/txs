<?php

declare(strict_types=1);

namespace App\Vmestecard;

use Generator;

/**
 * Provide data from the "vmestecard" account.
 */
interface TransactionsSourceInterface
{
    /**
     * Read the next transaction object.
     *
     * @return Generator|Transaction[]
     * @throws SourceReadErrorException
     */
    public function read(): Generator;
}