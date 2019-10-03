<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Regex\PregMatcher;

/**
 * Try to parse messages about refunds in format:
 *  "{account} {time} возврат покупки {amount}{currency symbol} {description} Баланс: XXXXX"
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * For example:
 *  - VISA0001 10:06 возврат покупки 123456.68р TITLE Баланс: 81692р
 *  - VISA0001 21.01.19 возврат покупки 1000р XXX Баланс: 10422.87р
 *
 * @see SberDatesTrait::resolveDate
 */
final class SberRefund implements MessageParserInterface
{
    use SberDatesTrait, SberRegexParserTrait;

    private const REGULAR_REFUND_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) возврат покупки (?<amount>[0-9.]+)\s?(?<currency>[а-яa-z]+) (?<description>.+?)? Баланс/ui';

    /**
     */
    public function __construct()
    {
        $this->setRegularExpression(PregMatcher::matchFirst(self::REGULAR_REFUND_REGEX));
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
     * @return Bill|null
     */
    private function createBill(Message $sms, array $matches): ?Bill
    {
        $amount = (float)str_replace(',', '.', $matches['amount']);

        return Composer::incomeBill()
            ->setAmount($amount)
            ->setCurrency($matches['currency'])
            ->setAccount($matches['account'])
            ->setDescription('Возврат от ' . $matches['description'])
            ->setDate($this->resolveDate($sms, $matches['time']))
            ->getBill();
    }
}