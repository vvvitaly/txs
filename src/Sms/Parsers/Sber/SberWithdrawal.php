<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Regex\PregMatcher;

/**
 * Parse messages about withdrawals in the form:
 *  "{account} {time} Выдача {amount}{currency symbol} {description} Баланс: XXXXXX"
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * For example:
 * - VISA1111 10:06 Выдача 150000р OSB 9999 9999 Баланс: 68892.69р
 * - VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р
 *
 * @see SberDatesTrait::resolveDate
 */
final class SberWithdrawal implements MessageParserInterface
{
    use SberDatesTrait, SberRegexParserTrait;

    private const REGULAR_WITHDRAWAL_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) Выдача (?<amount>[0-9.]+)\s?(?<currency>[а-яa-z]+) (?<description>.*?) Баланс/ui';

    /**
     */
    public function __construct()
    {
        $this->setRegularExpression(PregMatcher::matchFirst(self::REGULAR_WITHDRAWAL_REGEX));
        $this->setBillsFactory(function (Message $sms, array $matches): ?Bill {
            return $this->createBill($sms, $matches);
        });
    }

    /**
     * Create a bill instance from matches data.
     *
     * @param Message $sms
     * @param array $matches
     *
     * @return Bill
     */
    private function createBill(Message $sms, array $matches): Bill
    {
        $amount = (float)str_replace(',', '.', $matches['amount']);
        $description = 'Выдача / ' . $matches['currency'] . ', ' . $matches['description'];

        return Composer::expenseBill()
            ->setAmount($amount)
            ->setCurrency($matches['currency'])
            ->setAccount($matches['account'])
            ->setDescription($description)
            ->setDate($this->resolveDate($sms, $matches['time']))
            ->getBill();
    }
}