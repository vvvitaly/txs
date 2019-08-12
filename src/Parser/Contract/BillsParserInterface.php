<?php

declare(strict_types=1);

namespace App\Parser\Contract;

use App\Bills\BillsCollection;

/**
 * Parse bills from some source
 */
interface BillsParserInterface
{
    /**
     * Run parser
     *
     * @return BillsCollection
     */
    public function parse(): BillsCollection;
}