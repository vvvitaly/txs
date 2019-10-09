<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use Webmozart\Assert\Assert;

/**
 * PIN SMS parsed data
 */
final class PinMatches
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
     * @var string
     */
    public $account;

    /**
     * @param string $orderId
     * @param string $store
     * @param string $account
     */
    public function __construct(string $orderId, string $store, string $account)
    {
        $this->orderId = $orderId;
        $this->store = $store;
        $this->account = $account;
    }

    /**
     * Create an instance from matches obtained from PREG matcher. Matches array contains keys:
     * - orderId (required)
     * - store (store name, required)
     * - account (required)
     *
     * @param array $matches
     *
     * @return static
     */
    public static function fromPregMatches(array $matches): PinMatches
    {
        foreach (['orderId', 'store', 'account'] as $key) {
            Assert::keyExists($matches, $key);
        }

        return new static($matches['orderId'], $matches['store'], $matches['account']);
    }
}