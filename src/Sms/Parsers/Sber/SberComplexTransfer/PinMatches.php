<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use Webmozart\Assert\Assert;

/**
 * PIN SMS parsed data
 */
final class PinMatches
{
    /**
     * @var string
     */
    public $senderAccount;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $description;

    /**
     * @param string $senderAccount
     * @param float $amount
     */
    public function __construct(string $senderAccount, float $amount)
    {
        $this->senderAccount = $senderAccount;
        $this->amount = $amount;
    }

    /**
     * Create an instance from matches obtained from PREG matcher. Matches array contains keys:
     * - account (sender account, required)
     * - amount (required)
     * - currency
     * - description
     *
     * @param array $matches
     *
     * @return static
     */
    public static function fromPregMatches(array $matches): PinMatches
    {
        foreach (['account', 'amount'] as $key) {
            Assert::keyExists($matches, $key);
        }
        Assert::float($matches['amount']);

        $instance = new static($matches['account'], $matches['amount']);
        $instance->currency = $matches['currency'] ?? null;
        $instance->description = $matches['description'] ?? null;

        return $instance;
    }
}