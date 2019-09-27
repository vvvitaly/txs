<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\RegexParsingTrait;

/**
 * Parse PIN SMS with regular expressions
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
    public function parseSms(Message $message): ?TransferPinMessage
    {
        if (($matches = $this->match(self::PIN_REGEX, $message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);

            $isPartialReceiver = preg_match('/^\d+$/', $matches['receiver']) === 1;
            $matches['description'] = 'Перевод ' . ($isPartialReceiver ? 'на ****' : '') . $matches['receiver'];

            return TransferPinMessage::fromSms($message, $matches);
        }

        return null;
    }
}