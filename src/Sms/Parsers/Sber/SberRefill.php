<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Try to parse message card refilling in format:
 *  "{account} {time} зачисление {description} {amount}{currency symbol} {description} Баланс: XXXXX"
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * It seems, that if SMS was sent on the same day when the correspondence transaction was performed, then the "time"
 * format is used. Otherwise it uses the "date" format.
 *
 * For example:
 *  - VISA0001 10:06 зачисление 70292.68р VISA MONEY TRANSFER Баланс: 81692р
 *  - VISA0001 16:30 зачисление зарплаты 35000р Баланс: 115795.17р
 *  - VISA0001 19:58 зачисление страхового возмещения 6500р Баланс: 14763.42р
 *  - VISA0001 12:59 Зачисление 1000р ATM 60000111 Баланс: 10422.87р
 *
 * This parser skips messages about transfers between own accounts:
 *  - VISA7777 08:34 зачисление 200000р со вклада Баланс: 208892.69р
 */
final class SberRefill implements MessageParserInterface
{
    use SberValidationTrait, SberDatesTrait;

    private const REGULAR_REFILL_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) (?<description1>[зЗ]ачисление.*?) (?<amount>[0-9.]+)(?<currency>[а-яa-z]+)\s?(?<description2>.+?)? Баланс/ui';

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        if (preg_match(self::REGULAR_REFILL_REGEX, $sms->text, $matches, PREG_UNMATCHED_AS_NULL)) {
            return $this->parseMatches($sms, $matches);
        }

        return null;
    }

    /**
     * Create a bill instance from matches data.
     *
     * @param Message $sms
     * @param array $matches
     *
     * @return Bill|null
     */
    private function parseMatches(Message $sms, array $matches): ?Bill
    {
        $amount = (float)str_replace(',', '.', $matches['amount']);
        $description = $matches['description2'] ?? $matches['description1'];

        if (strpos($description, 'вклад') !== false) {
            return null;
        }

        $description .= ', ' . $matches['account'];

        return new Bill(
            new Amount($amount, $matches['currency']),
            $matches['account'],
            new BillInfo($this->resolveDate($sms, $matches['time']), $description)
        );
    }
}