<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use Webmozart\Assert\Assert;

/**
 * Confirmation SMS parsed data
 */
final class ConfirmationMatches
{
    /**
     * @var string
     */
    public $orderId;

    /**
     * @var string
     */
    public $store;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @param string $orderId
     * @param string $store
     * @param float $amount
     * @param string $currency
     */
    public function __construct(string $orderId, string $store, float $amount, string $currency)
    {
        $this->orderId = $orderId;
        $this->store = $store;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Create an instance from matches obtained from PREG matcher. Matches array must contain keys:
     * - orderId
     * - store
     * - amount (float)
     * - currency
     *
     * @param array $matches
     *
     * @return static
     */
    public static function fromPregMatches(array $matches): ConfirmationMatches
    {
        foreach (['orderId', 'store', 'amount', 'currency'] as $key) {
            Assert::keyExists($matches, $key);
        }

        return new static($matches['orderId'], $matches['store'], $matches['amount'], $matches['currency']);
    }
}