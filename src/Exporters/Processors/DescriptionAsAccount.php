<?php

declare(strict_types=1);

namespace App\Exporters\Processors;

use App\Core\Export\Data\Transaction;

/**
 * Use transaction description (or memo) as account name in splits. It can be useful for repeated transactions, when
 * you can assign every such transactions with GnuCash account (GnuCash allows it in Import Matcher).
 *
 * For example:
 * 1) $5 bill for tomatoes produces the next transaction:
 *  - amount = -5
 *  - currency = "USD"
 *  - description = "tomatoes"
 *  - splits:
 *      * [amount = 5, account = null, memo = null]
 *  This processor will use description ("tomatoes") for split account.
 *
 * 2) $15 bill for shopping ($5 for tomatoes, $7 for apples, $3 for coffee) produces the next transaction:
 *  - amount = -15
 *  - currency = "USD"
 *  - description = "shopping"
 *  - splits:
 *      * [amount = 5, account = null, memo = "tomatoes"]
 *      * [amount = 7, account = null, memo = "apples"]
 *      * [amount = 3, account = null, memo = "coffee"]
 *  This processor will use memo ("tomatoes", "apples", "coffee") for every split account.
 *
 */
final class DescriptionAsAccount implements ProcessorInterface
{
    use ProcessorChainTrait;

    /**
     * @inheritDoc
     */
    public function process(Transaction $transaction): void
    {
        $isSplit = count($transaction->splits) > 1;

        foreach ($transaction->splits as $split) {
            if ($split->account) {
                continue;
            }
            $split->account = $isSplit ? $split->memo : $transaction->description;
        }

        $this->next($transaction);
    }
}