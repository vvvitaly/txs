<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\RegexParsingTrait;
use vvvitaly\txs\Sms\Parsers\Sber\SberDatesTrait;

/**
 * Parse transfer SMS in format:
 *  - "{account} {time} перевод {amount}{currency symbol} Баланс: XXXXX.YY{currency}"
 *
 * Time might have following formats:
 * - HH:MM
 * - DD.MM.YY
 * - DD.MM.YY HH:MM
 *
 * For example:
 *  - "VISA0001 08:17 перевод 455р Баланс: 7673.22р"
 */
final class ConfirmationSmsParser implements ConfirmationSmsParserInterface
{
    use RegexParsingTrait, SberDatesTrait;

    private const TRANSFER_REGEX = [
        '/^(?<account>[A-Z\d]+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) перевод (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) Баланс:/ui',
    ];

    /**
     * @inheritDoc
     */
    public function parseSms(Message $message): ?ConfirmationMessage
    {
        if (($matches = $this->match(self::TRANSFER_REGEX, $message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);
            $matches['time'] = $this->resolveDate($message, $matches['time']);

            return ConfirmationMessage::fromSms($message, $matches);
        }

        return null;
    }
}