<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Regex\MatcherInterface;
use vvvitaly\txs\Sms\Parsers\Regex\PregMatcher;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberDatesTrait;

/**
 * Resolve order step for the given SMS.
 *
 * @see PinMessageStep
 * @see ConfirmationMessageStep
 */
final class OrderStepParser implements OperationStepParserInterface
{
    use SberDatesTrait;

    private const PIN_REGEX = '/^Проверьте реквизиты платежа: интернет-магазин (?<store>.*?), заказ: (?<orderId>.*?), сумма: (?<amount>[0-9.,]+) (?<currency>[а-яa-z]+). Для оплаты с карты (?<account>[A-Z\d]+) отправьте пароль \d+/ui';
    private const CONFIRMATION_REGEX = '/^Заказ (?<orderId>.*?) в магазине (?<store>.*?) на сумму (?<amount>[0-9.,]+) (?<currency>[а-яa-z]+)\.? успешно оплачен./ui';

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
        $this->pinMatcher = PregMatcher::matchFirst(self::PIN_REGEX);
        $this->confirmationMatcher = PregMatcher::matchFirst(self::CONFIRMATION_REGEX);
    }

    /**
     * @inheritDoc
     */
    public function parseStep(Message $message): ?OperationStepInterface
    {
        if (($matches = $this->pinMatcher->match($message->text)) !== null) {
            return new PinMessageStep($message, PinMatches::fromPregMatches($matches));
        }

        if (($matches = $this->confirmationMatcher->match($message->text)) !== null) {
            $matches['amount'] = (float)str_replace(',', '.', $matches['amount']);

            return new ConfirmationMessageStep($message, ConfirmationMatches::fromPregMatches($matches));
        }

        return null;
    }
}