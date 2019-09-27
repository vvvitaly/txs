<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;

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
    use SberValidationTrait, SberDatesTrait, RegexParsingTrait;

    private const REGULAR_REFUND_REGEX = '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) возврат покупки (?<amount>[0-9.]+)(?<currency>[а-яa-z]+)\s?(?<description>.+?)? Баланс/ui';

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        $matches = $this->match([self::REGULAR_REFUND_REGEX], $sms->text);
        if ($matches) {
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

        return Composer::incomeBill()
            ->setAmount($amount)
            ->setCurrency($matches['currency'])
            ->setAccount($matches['account'])
            ->setDescription('Возврат от ' . $matches['description'])
            ->setDate($this->resolveDate($sms, $matches['time']))
            ->getBill();
    }
}