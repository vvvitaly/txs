<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms;

use DateTimeImmutable;

/**
 * SMS DTO
 */
final class Message
{
    /**
     * Sender address
     * @var string
     */
    public $from;

    /**
     * Receiving date
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * Message text
     * @var string
     */
    public $text;

    /**
     * @param string $from
     * @param DateTimeImmutable $date
     * @param string $text
     */
    public function __construct(string $from, DateTimeImmutable $date, string $text)
    {
        $this->from = $from;
        $this->date = $date;
        $this->text = $text;
    }
}