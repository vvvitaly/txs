<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Regex\MatcherInterface;
use vvvitaly\txs\Sms\Parsers\Regex\PregMatcher;
use vvvitaly\txs\Sms\Parsers\Regex\RegexList;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberDatesTrait;

/**
 * Parse messages about sber transfers which don't contain descriptions. Transfer operation contains two steps:
 * 1. Message with pin code: it contains sender account, transfer amount and description;
 * 2. Confirmation message after transfer was finished. It contains account, amount and operation time.
 *
 * @see PinMessageStep
 * @see ConfirmationMessageStep
 */
final class TransferStepParser implements OperationStepParserInterface
{
    use SberDatesTrait;

    private const PIN_SMS_REGEX = '/^Для перевода (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) получателю (?<receiver>.+?) с карты (?<account>[A-Z\d]+) отправьте код \d+/ui';
    private const PIN_ONLINE_REGEX = '/^Проверьте реквизиты перевода: карта списания \*\*\*\* (?<account>\d+), карта зачисления \*\*\*\* (?<receiver>.+?), сумма (?<amount>[0-9.,]+) (?<currency>[а-яa-z]+). Пароль для подтверждения - \d+/ui';

    private const CONFIRMATION_REGEX = '/^(?<account>[A-Z\d]+) (?<time>(?:\d{2}.\d{2}.\d{2})?\s?(?:\d{2}:\d{2})?) перевод (?<amount>[0-9.]+)(?<currency>[а-яa-z]+) Баланс:/ui';

    /**
     * @var MatcherInterface
     */
    private $pinMatcher;

    /**
     * @var MatcherInterface
     */
    private $confirmationMatcher;

    public function __construct()
    {
        $this->pinMatcher = new RegexList(
            PregMatcher::matchFirst(self::PIN_SMS_REGEX),
            PregMatcher::matchFirst(self::PIN_ONLINE_REGEX)
        );
        $this->confirmationMatcher = PregMatcher::matchFirst(self::CONFIRMATION_REGEX);
    }


    /**
     * @inheritDoc
     */
    public function parseStep(Message $message): ?OperationStepInterface
    {
        if (($matches = $this->pinMatcher->match($message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);

            $isPartialReceiver = preg_match('/^\d+$/', $matches['receiver']) === 1;
            $matches['description'] = 'Перевод ' . ($isPartialReceiver ? 'на ****' : '') . $matches['receiver'];

            return new PinMessageStep($message, PinMatches::fromPregMatches($matches));
        }

        if (($matches = $this->confirmationMatcher->match($message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);
            $matches['time'] = $this->resolveDate($message, $matches['time']);

            return new ConfirmationMessageStep($message, ConfirmationMatches::fromPregMatches($matches));
        }

        return null;
    }
}