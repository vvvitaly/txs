<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\SberComplexOperationParser;

/**
 * Try to parse messages about transfers from card without description. Such messages are skipped by SberTransfer
 * parser. This parser takes description of the transfer from pin/confirm SMS. Pin/confirm messages format:
 *  - "Для перевода {amount}{currency symbol} получателю {receiver name} на {receiver account} с карты {account}
 *    отправьте код {pin} на 900.Комиссия не взимается"
 *  - "Проверьте реквизиты перевода: карта списания **** {account}, карта зачисления **** {receiver account}, сумма
 *    {amount} {currency}. Пароль для подтверждения - {pin}. Никому не сообщайте пароль."
 * Transfer messages format:
 *  "{account} {time} перевод {amount}{currency symbol} Баланс: XXXXX.YY{currency}"
 *
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * Pin and transfer messages contain the same account and amount values.
 *
 * For example.
 * Pin message: "Для перевода 455р получателю SOMEBODY на VISA0002 с карты VISA0001 отправьте код 27805 на 900. К
 * омиссия не взимается"
 * Corresponding transfer message: "VISA0001 08:17 перевод 455р Баланс: 7673.22р"
 *
 * Pin message: "Проверьте реквизиты перевода: карта списания **** 0001, карта зачисления **** 0002, сумма 17000,00
 * RUB.
 * Пароль для подтверждения - 63659. Никому не сообщайте пароль."
 * And the transfer message is "VISA0001 21:28 перевод 17000р Баланс: 10148.64р"
 *
 * It skips messages about transfers that contains description.
 */
final class SberComplexTransferParserFactory
{
    /**
     * @return SberComplexOperationParser
     */
    public function getParser(): SberComplexOperationParser
    {
        return new SberComplexOperationParser(
            new TransferStepParser(),
            new BillsFactory()
        );
    }
}