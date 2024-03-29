<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Regex\PregMatcher;
use vvvitaly\txs\Sms\Parsers\Regex\RegexList;

/**
 * Try to parse messages about transfers between accounts (from card to account or from account to card). Such messages
 * have the following format:
 *  "С Вашей [карты|счета] {account} произведен перевод на [счет|карту] {description} на сумму {amount} {currency}"
 *
 * For example:
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
 */
final class SberTransfer implements MessageParserInterface
{
    use SberDatesTrait, SberRegexParserTrait;

    private const TRANSFER_REGEX_ACCOUNT =
        '/^С Ваше(?:й|го) (?:карты|счета) (?<account>.+?) произведен перевод на (?:счет|карту) № (?<description>.+?) на сумму (?<amount>[0-9.,]+) (?<currency>[A-Z]{3}).$/ui';

    private const TRANSFER_REGEX_REGULAR =
        '/^(?<account>\S+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) (?<description1>перевод.*?) (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) (?<description2>.+?) Баланс/ui';

    /**
     */
    public function __construct()
    {
        $this->setRegularExpression(new RegexList(
            PregMatcher::matchFirst(self::TRANSFER_REGEX_ACCOUNT),
            PregMatcher::matchFirst(self::TRANSFER_REGEX_REGULAR)
        ));
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
        if (isset($matches['description'])) {
            $description = $matches['description'];
        } elseif (isset($matches['description1'], $matches['description2'])) {
            $description = $matches['description2'];
        }

        $nonAccountChars = ['*', ' '];
        $account = str_replace($nonAccountChars, '', $matches['account']);
        $description = str_replace($nonAccountChars, '', $description);
        $date = isset($matches['time'])
            ? $this->resolveDate($sms, $matches['time'])
            : $sms->date;

        return Composer::expenseBill()
            ->setAmount($amount)
            ->setCurrency($matches['currency'])
            ->setAccount($account)
            ->setDescription('Перевод на ' . $description)
            ->setDate($date)
            ->getBill();
    }
}