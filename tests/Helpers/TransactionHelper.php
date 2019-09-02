<?php

declare(strict_types=1);

namespace tests\Helpers;

use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Core\Export\Data\TransactionSplit;

/**
 * Testing utilities for work with transactions
 */
final class TransactionHelper
{
    /**
     * Create testing transactions by array
     *
     * @param array $fields
     *
     * @return Transaction
     */
    public static function createTransaction(array $fields): Transaction
    {
        $tx = new Transaction();
        foreach ($fields as $key => $value) {
            if ($key === 'splits') {
                foreach ($value as $split) {
                    if (!$split instanceof TransactionSplit) {
                        $splitObj = new TransactionSplit();
                        foreach ($split as $splitKey => $splitValue) {
                            $splitObj->$splitKey = $splitValue;
                        }
                    } else {
                        $splitObj = $split;
                    }

                    $tx->splits[] = $splitObj;
                }
            } else {
                $tx->$key = $value;
            }
        }

        return $tx;
    }
}