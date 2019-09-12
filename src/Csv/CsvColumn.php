<?php

declare(strict_types=1);

namespace vvvitaly\txs\Csv;

/**
 * Columns types enum
 */
final class CsvColumn
{
    public const DATE = 'date';
    public const ACCOUNT = 'account';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';
    public const DESCRIPTION = 'description';
    public const IGNORE = '-';
}
