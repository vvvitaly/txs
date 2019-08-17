<?php

declare(strict_types=1);

namespace App\Sms;

use DateTimeImmutable;

/**
 * SMS DTO
 */
final class Sms
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
    public $message;

    /**
     * @param string $from
     * @param DateTimeImmutable $date
     * @param string $message
     */
    public function __construct(string $from, DateTimeImmutable $date, string $message)
    {
        $this->from = $from;
        $this->date = $date;
        $this->message = $message;
    }
}