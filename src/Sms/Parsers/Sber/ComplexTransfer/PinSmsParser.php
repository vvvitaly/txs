<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\RegexParsingTrait;

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
final class PinSmsParser implements PinSmsParserInterface
{
    use RegexParsingTrait;

    private const PIN_REGEX = [
        '/^Для перевода (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) получателю (?<receiver>.+?) с карты (?<account>[A-Z\d]+) отправьте код \d+/ui',
        '/^Проверьте реквизиты перевода: карта списания \*\*\*\* (?<account>\d+), карта зачисления \*\*\*\* (?<receiver>.+?), сумма (?<amount>[0-9.,]+) (?<currency>[а-яa-z]+). Пароль для подтверждения - \d+/ui',
    ];

    /**
     * @inheritDoc
     */
    public function parseSms(Message $message): ?PinMessage
    {
        if (($matches = $this->match(self::PIN_REGEX, $message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);

            $isPartialReceiver = preg_match('/^\d+$/', $matches['receiver']) === 1;
            $matches['description'] = 'Перевод ' . ($isPartialReceiver ? 'на ****' : '') . $matches['receiver'];

            return PinMessage::fromSms($message, $matches);
        }

        return null;
    }
}