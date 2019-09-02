<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Export\Data;

use DateTimeImmutable;

/**
 * Exported GnuCash-like transaction DTO. Obtained from exporter and used in writers.
 */
final class Transaction
{
    /**
     * @var DateTimeImmutable|null Transaction date
     */
    public $date;

    /**
     * @var string|null Transaction group ID
     */
    public $id;

    /**
     * @var string|null Transaction number
     */
    public $num;

    /**
     * @var string|null Transaction account name
     */
    public $account;

    /**
     * @var string|null Transaction description
     */
    public $description;

    /**
     * @var float Transaction total amount value
     */
    public $amount;

    /**
     * @var string|null Transaction currency code
     */
    public $currency;

    /**
     * @var TransactionSplit[] Transaction parts
     */
    public $splits = [];
}