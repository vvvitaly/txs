<?php

declare(strict_types=1);

namespace App\Sms\Parsers;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Sms\MessageParserInterface;
use App\Sms\Sms;

/**
 * Parse messages about withdrawals:
 *  "{account} {time} Выдача {amount}{currency symbol} {description} Баланс: XXXXXX"
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * It seems, that if SMS was sent on the same day when the correspondence transaction was performed, then the "time"
 * format is used. Otherwise it uses the "date" format.
 *
 * For example:
 * - VISA1111 10:06 Выдача 150000р OSB 9999 9999 Баланс: 68892.69р
 * - VISA1111 11:31 Выдача 3400р ATM 00000001 Баланс: 16639.63р
 */
final class SberWithdrawal implements MessageParserInterface
{
    use SberValidationTrait, SberDatesTrait;

    private const REGULAR_WITHDRAWAL_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) Выдача (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) (?<description>.*?) Баланс/u';

    /**
     * @inheritDoc
     */
    public function parse(Sms $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        if (preg_match(self::REGULAR_WITHDRAWAL_REGEX, $sms->message, $matches, PREG_UNMATCHED_AS_NULL)) {
            return $this->parseMatches($sms, $matches);
        }

        return null;
    }

    /**
     * Create a bill instance from matches data.
     *
     * @param Sms $sms
     * @param array $matches
     *
     * @return Bill
     */
    private function parseMatches(Sms $sms, array $matches): Bill
    {
        $amount = (float)str_replace(',', '.', $matches['amount']);

        return new Bill(
            new Amount($amount, $matches['currency']),
            $matches['account'],
            new BillInfo($this->resolveDate($sms, $matches['time']), 'Выдача ' . $matches['description'])
        );
    }
}