<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

/**
 * Confirmation SMS parsed data
 */
final class ConfirmationMatches
{
    /**
     * @var string
     */
    public $account;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var DateTimeImmutable
     */
    public $confirmationDate;

    /**
     * @param string $account
     * @param float $amount
     * @param DateTimeImmutable $confirmationDate
     */
    public function __construct(string $account, float $amount, DateTimeImmutable $confirmationDate)
    {
        $this->account = $account;
        $this->amount = $amount;
        $this->confirmationDate = $confirmationDate;
    }

    /**
     * Create an instance from matches obtained from PREG matcher. Matches array must contain keys:
     * - account
     * - amount
     * - time (operation time)
     *
     * @param array $matches
     *
     * @return static
     */
    public static function fromPregMatches(array $matches): ConfirmationMatches
    {
        foreach (['account', 'amount', 'time'] as $key) {
            Assert::keyExists($matches, $key);
        }
        Assert::isInstanceOf($matches['time'], DateTimeImmutable::class);

        return new static($matches['account'], $matches['amount'], $matches['time']);
    }
}