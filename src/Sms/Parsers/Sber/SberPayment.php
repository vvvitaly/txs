<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Try to parse message about payments by card. Such messages have the following format:
 *  "{account} {time} Оплата {amount}{currency symbol} {description} Баланс: XXXXX"
 * Or some special message:
 *  "{account} {time} оплата годового обслуживания карты {amount}{currency symbol} Баланс: XXXXX"
 *  "{account} {time} мобильный банк за [date range DD.MM-DD.MM] {amount}{currency symbol} Баланс: XXXXX"
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * It seems, that if SMS was sent on the same day when the correspondence transaction was performed, then the "time"
 * format is used. Otherwise it uses the "date" format.
 *
 * For example:
 *
 * - VISA1234 02:38 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р
 * - VISA1234 06.04.19 02:38 Оплата 100р TELE2 (9001234567) Баланс: 14074.22р
 * - ECMC4321 03:06 оплата годового обслуживания карты 450р Баланс: 12345.62р
 * - VISA2288 06.04.19 мобильный банк за 06.04-05.05 60р Баланс: 11227.69
 *
 * This parser can not parse messages without payment description:
 * - VISA1111 21:56 Оплата 610.10р Баланс: 21237.54р
 * - Пароль для подтверждения платежа - 85596. Оплата 14240,00 RUB с карты **** 1111. Реквизиты: XXXXXX
 */
final class SberPayment implements MessageParserInterface
{
    use SberValidationTrait, SberDatesTrait;

    private const REGULAR_PAYMENT_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) Оплата (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) (?<description>.*?) Баланс/ui';
    private const ANNUAL_MAINTENANCE_PAYMENT_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) (?<description>оплата годового обслуживания карты) (?<amount>[0-9.,]+)(?<currency>[а-яa-z]+) Баланс/ui';
    private const MONTHLY_PAYMENT_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) (?<description>мобильный банк) за [0-9.-]+ (?<amount>[0-9.,]+)(?<currency>[а-яa-z]+) Баланс/ui';

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        $regexes = [
            [self::REGULAR_PAYMENT_REGEX, true],
            [self::ANNUAL_MAINTENANCE_PAYMENT_REGEX, false],
            [self::MONTHLY_PAYMENT_REGEX, false],
        ];

        foreach ($regexes as [$regex, $addPrefix]) {
            $matches = [];
            if (preg_match($regex, $sms->text, $matches, PREG_UNMATCHED_AS_NULL)) {
                return $this->parseMatches($sms, $matches, $addPrefix);
            }
        }

        return null;
    }

    /**
     * Create a bill instance from matches data.
     *
     * @param Message $sms
     * @param array $matches
     * @param bool $addPrefix add message prefix
     *
     * @return Bill
     */
    private function parseMatches(Message $sms, array $matches, bool $addPrefix): Bill
    {
        $amount = (float)str_replace(',', '.', $matches['amount']);
        $description = ($addPrefix ? 'Оплата ' : '') . $matches['description'];

        return Composer::expenseBill()
            ->setAmount($amount)
            ->setCurrency($matches['currency'])
            ->setAccount($matches['account'])
            ->setDescription($description)
            ->setDate($this->resolveDate($sms, $matches['time']))
            ->getBill();
    }
}