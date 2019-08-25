<?php

declare(strict_types=1);

namespace App\Sms\Parsers\Sber;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Sms\Message;
use App\Sms\Parsers\MessageParserInterface;

/**
 * Try to parse message about transfer between accounts (from card to account or from account to card). Such messages
 * have the following format:
 *  "С Вашей [карты|счета] {account} произведен перевод на [счет|карту] {description} на сумму {amount} {currency}"
 *
 * For example:
 *
 * - С Вашей карты **** 1234 произведен перевод на счет № 10000000000000000123 на сумму 430,00 RUB.
 * - С Вашего счета 11111111111111111857 произведен перевод на карту № **** 4321 на сумму 19000,00 RUB.
 * - С Вашей карты **** 7777 произведен перевод на карту № **** 0001 на сумму 6154,00 RUB.
 *
 * It skips messages like:
 * - Проверьте реквизиты перевода: карта списания **** 1234, карта зачисления **** 4321, сумма 5000,00 RUB. Пароль для
 * подтверждения - 10001. Никому не сообщайте пароль.
 * - VISA1111 22:50 перевод 5000р Баланс: 14174.22р
 * - Для перевода 4000р получателю SOME PERSON X. на карту VISA4444 с карты VISA7777 отправьте код 49762 на номер 900.
 * Комиссия не взимается. Добавьте сообщение получателю, набрав его после кода. Например, 49762 сообщение получателю.
 *
 */
final class SberTransfer implements MessageParserInterface
{
    use SberValidationTrait, SberDatesTrait;

    private const REGULAR_REFILL_REGEX = '/^С Ваше(?:й|го) (?:карты|счета) (?<account>.+?) произведен перевод на (?:счет|карту) № (?<description>.+?) на сумму (?<amount>[0-9.,]+) (?<currency>[A-Z]{3}).$/ui';

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

        $nonAccountChars = ['*', ' '];
        $account = str_replace($nonAccountChars, '', $matches['account']);
        $description = str_replace($nonAccountChars, '', $matches['description']);

        return new Bill(
            new Amount($amount, $matches['currency']),
            $account,
            new BillInfo($sms->date, 'Перевод на ' . $description)
        );
    }
}