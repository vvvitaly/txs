<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\RegexParsingTrait;
use vvvitaly\txs\Sms\Parsers\Sber\SberDatesTrait;

/**
 * Default realization for parsing transfer messages (after pin)
 */
final class TransferSmsParser implements TransferSmsParserInterface
{
    use RegexParsingTrait, SberDatesTrait;

    private const TRANSFER_REGEX = [
        '/^(?<account>[A-Z\d]+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) перевод (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) Баланс:/ui',
    ];

    /**
     * @inheritDoc
     */
    public function parseSms(Message $message): ?TransferMessage
    {
        if (($matches = $this->match(self::TRANSFER_REGEX, $message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);
            $matches['time'] = $this->resolveDate($message, $matches['time']);

            return TransferMessage::fromSms($message, $matches);
        }

        return null;
    }
}