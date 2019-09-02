<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Export\Data;

/**
 * Transaction split DTO
 */
final class TransactionSplit
{
    /**
     * @var float Split amount value
     */
    public $amount;

    /**
     * @var string Destination account name
     */
    public $account;

    /**
     * @var string Split description
     */
    public $memo;
}