<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\PinParser;

use DateTimeImmutable;
use vvvitaly\txs\Sms\Message;
use Webmozart\Assert\Assert;

/**
 * DTO for searching pin messages
 */
final class PinMessage
{
    /**
     * @var DateTimeImmutable
     */
    public $receivingDate;

    /**
     * @var string
     */
    public $account;

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
     * Create instance from parsed SMS. Array of text matches should contain the next keys:
     * - account
     * - amount
     * - currency
     * - description
     *
     * @param \vvvitaly\txs\Sms\Message $message
     * @param array $textMatches
     *
     * @return static
     */
    public static function fromSms(Message $message, array $textMatches): self
    {
        Assert::keyExists($textMatches, 'account', 'Account is required');
        Assert::keyExists($textMatches, 'amount', 'Amount is required');
        Assert::float($textMatches['amount']);
        Assert::keyExists($textMatches, 'currency', 'Currency is required');
        Assert::keyExists($textMatches, 'description', 'Description is required');

        $instance = new static();

        $instance->receivingDate = $message->date;
        $instance->account = $textMatches['account'];
        $instance->amount = $textMatches['amount'];
        $instance->currency = $textMatches['currency'];
        $instance->description = $textMatches['description'];

        return $instance;
    }
}