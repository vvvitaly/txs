<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillItem;
use vvvitaly\txs\Core\Bills\BillsCollection;
use Webmozart\Assert\Assert;

/**
 * Get pretty string with bill.
 *
 * @param Bill $bill
 *
 * @return string
 */
function dumpBill(Bill $bill): string
{
    $print = static function (string $value, int $fieldLength, string $alignment = 'r', ?string $filler = null) {
        $len = strlen($value) - mb_strlen($value) + $fieldLength;
        $alig = $alignment === 'l' ? '-' : '';
        $fill = $filler ? "'{$filler}" : '';

        $fmt = "%{$alig}{$fill}{$len}s";

        return sprintf($fmt, $value);
    };

    $buf = '';

    $buf .= sprintf('%12s: ', $bill->getInfo()->getDate()->format('d.m.Y'));
    $buf .= $print($bill->getInfo()->getDescription() ?: '', 70, 'l');
    $buf .= sprintf('%-.2f', $bill->getAmount()->getValue());
    $buf .= sprintf(' %3s', $bill->getAmount()->getCurrency() ?: 'RUB');
    $buf .= sprintf(' > %s', $bill->getAccount());

    foreach ($bill->getItems() as $item) {
        $ibuf = '';
        $ibuf .= str_repeat(' ', 10);
        $ibuf .= $print($item->getDescription() ?: '', 74, 'l', '.');
        $ibuf .= sprintf('%-.2f', $item->getAmount()->getValue());

        $buf .= "\n" . $ibuf;
    }

    return $buf;
}

/**
 * Generate a bill instance with random values
 *
 * @return Bill
 * @throws Exception
 */
function randomBill(): Bill
{
    /**
     * Random currency name or empty string (with 50% chance)
     * @return string
     */
    $randCurrency = static function (): string {
        if (random_int(1, 100) <= 50) {
            return '';
        }

        $currencies = ['RUB', 'USD', 'EUR'];

        return $currencies[array_rand($currencies)];
    };

    /**
     * Random amount value from 0.01 to $upperLimit
     *
     * @param float $upperLimit
     *
     * @return float
     */
    $randAmount = static function (float $upperLimit = 10000): float {
        return random_int(1, (int)($upperLimit * 100)) / 100;
    };

    /**
     * Random account name
     * @return string
     */
    $randAccount = static function (): string {
        $accounts = ['cash', 'deposit1', 'deposit2', 'credit card', 'visa'];

        return $accounts[array_rand($accounts)];
    };

    /**
     * Random description
     *
     * @return string
     */
    $randDescription = static function (): string {
        return uniqid('test ', false);
    };

    $totalAmount = $randAmount(); // total bill amount

    $date = new DateTimeImmutable();
    /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
    $date = $date->modify('- ' . random_int(0, 3600 * 24 * 31) . ' seconds');

    $hasItems = random_int(1, 100) > 60; // 60% chance that bill has items
    $items = [];
    if ($hasItems) {
        $numItems = random_int(2, 7); // items count
        $amountLimit = $totalAmount / $numItems * 0.2; // minimal amount value for each item
        $availableAmount = $totalAmount;

        foreach (range(1, $numItems) as $i) {
            $leftItems = $numItems - $i;
            $itemAmount = $availableAmount; // for last item
            if ($i !== $numItems) {
                $limit = $availableAmount - $leftItems * $amountLimit;
                $itemAmount = $randAmount($limit);
            }

            $items[] = new BillItem($randDescription(), new Amount($itemAmount));
            $availableAmount -= $itemAmount;
        }
    }

    return new Bill(
        new Amount($totalAmount, $randCurrency()),
        $randAccount(),
        new BillInfo($date, $randDescription(), '#' . random_int(1111, 2222)),
        $items
    );
}

/**
 * Generate random bills collection
 *
 * @param int $maxBills
 *
 * @return BillsCollection
 * @throws Exception
 */
function randomBillsCollection(int $maxBills): BillsCollection
{
    Assert::greaterThanEq($maxBills, 1);

    $numBills = random_int(1, $maxBills);

    $bills = [];
    for ($i = 0; $i < $numBills; $i++) {
        $bills[] = randomBill();
    }

    return new BillsCollection(...$bills);
}