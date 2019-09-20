<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

/**
 * Try to parse message about purchases by card. Such messages have the following format:
 *  "{account} {time} Покупка {amount}{currency symbol} {description} Баланс: XXXXXX"
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * For example:
 * - VISA8413 20:46 Покупка 30р ENERGY POINT Баланс: 2261.20р
 *
 * @see SberDatesTrait::resolveDate
 */
final class SberPurchase implements MessageParserInterface
{
    use SberValidationTrait, SberDatesTrait;

    private const REGULAR_PURCHASE_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) Покупка (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) (?<description>.*?) Баланс/ui';

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        if (preg_match(self::REGULAR_PURCHASE_REGEX, $sms->text, $matches, PREG_UNMATCHED_AS_NULL)) {
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
     * @return Bill
     */
    private function parseMatches(Message $sms, array $matches): Bill
    {
        $amount = (float)str_replace(',', '.', $matches['amount']);

        return Composer::expenseBill()
            ->setAmount($amount)
            ->setCurrency($matches['currency'])
            ->setAccount($matches['account'])
            ->setDescription($matches['description'])
            ->setDate($this->resolveDate($sms, $matches['time']))
            ->getBill();
    }
}