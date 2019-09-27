<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use DateTimeImmutable;
use vvvitaly\txs\Sms\Message;
use Webmozart\Assert\Assert;

/**
 * SMS data about transfer
 */
final class TransferMessage
{
    /**
     * @var DateTimeImmutable SMS receiving date
     */
    public $receivingDate;

    /**
     * @var DateTimeImmutable real transfer date
     */
    public $transferDate;

    /**
     * @var string
     */
    public $account;

    /**
     * @var float
     */
    public $amount;

    /**
     * Create instance from parsed SMS. Array of text matches should contain the next keys:
     * - date (real transfer date)
     * - account
     * - amount
     *
     * @param \vvvitaly\txs\Sms\Message $message
     * @param array $textMatches
     *
     * @return static
     */
    public static function fromSms(Message $message, array $textMatches): self
    {
        Assert::keyExists($textMatches, 'time', 'Time is required');
        Assert::isInstanceOf($textMatches['time'], DateTimeImmutable::class);
        Assert::keyExists($textMatches, 'account', 'Account is required');
        Assert::keyExists($textMatches, 'amount', 'Amount is required');
        Assert::float($textMatches['amount']);

        $instance = new static();

        $instance->receivingDate = $message->date;
        $instance->transferDate = $textMatches['time'];
        $instance->account = $textMatches['account'];
        $instance->amount = $textMatches['amount'];

        return $instance;
    }
}