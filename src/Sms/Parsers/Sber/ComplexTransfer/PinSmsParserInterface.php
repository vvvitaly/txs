<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;

/**
 * Parse PIN messages for transfer in format:
 * - "Для перевода {amount}{currency symbol} получателю {receiver name} на {receiver account} с карты {account}
 *    отправьте код {pin} на 900.Комиссия не взимается"
 *  - "Проверьте реквизиты перевода: карта списания **** {account}, карта зачисления **** {receiver account}, сумма
 *    {amount} {currency}. Пароль для подтверждения - {pin}. Никому не сообщайте пароль."
 *
 * For example:
 *  - "Для перевода 455р получателю SOMEBODY на VISA0002 с карты VISA0001 отправьте код 27805 на 900. К
 * омиссия не взимается"
 *  - "Проверьте реквизиты перевода: карта списания **** 0001, карта зачисления **** 0002, сумма 17000,00 RUB.
 * Пароль для подтверждения - 63659. Никому не сообщайте пароль."
 */
interface PinSmsParserInterface
{
    /**
     * Parse the message. If message is NOT PIN SMS, returns NULL.
     *
     * @param \vvvitaly\txs\Sms\Message $message
     *
     * @return \vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferPinMessage|null
     */
    public function parseSms(Message $message): ?TransferPinMessage;
}