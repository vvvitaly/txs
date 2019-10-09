<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberComplexTransfer;

use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\OperationStepInterface;

/**
 * Message with PIN code before sber transfer operation. Such messages have the following format:
 * - "Для перевода {amount}{currency symbol} получателю {receiver name} на {receiver account} с карты {account}
 *    отправьте код {pin} на 900.Комиссия не взимается"
 * - "Проверьте реквизиты перевода: карта списания **** {account}, карта зачисления **** {receiver account}, сумма
 *    {amount} {currency}. Пароль для подтверждения - {pin}. Никому не сообщайте пароль."
 *
 * For example:
 *  - "Для перевода 455р получателю SOMEBODY на VISA0002 с карты VISA0001 отправьте код 27805 на 900. Комиссия не
 * взимается"
 *  - "Проверьте реквизиты перевода: карта списания **** 0001, карта зачисления **** 0002, сумма 17000,00 RUB. Пароль
 * для подтверждения - 63659. Никому не сообщайте пароль."
 *
 * Pin messages are NOT terminal in transfer operation. They can be related only to following confirmation step.
 */
final class PinMessageStep implements OperationStepInterface
{
    /**
     * @var Message
     */
    private $originSms;

    /**
     * @var PinMatches
     */
    private $parsedMatches;

    /**
     * @var int
     */
    private $pinLifetime;

    /**
     * @param Message $originSms
     * @param PinMatches $parsedMatches
     * @param int $pinLifetime Maximum time in seconds between receiving PIN message and transfer message
     */
    public function __construct(
        Message $originSms,
        PinMatches $parsedMatches,
        int $pinLifetime = 600
    ) {
        $this->originSms = $originSms;
        $this->parsedMatches = $parsedMatches;
        $this->pinLifetime = $pinLifetime;
    }

    /**
     * @inheritDoc
     */
    public function isTerminalStep(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isRelatedToStep(OperationStepInterface $operationStep): bool
    {
        if (!$operationStep instanceof ConfirmationMessageStep) {
            return false;
        }

        /** @var ConfirmationMessageStep $operationStep */
        return $operationStep->isRelatedToStep($this);
    }

    /**
     * @return Message
     */
    public function getOriginSms(): Message
    {
        return $this->originSms;
    }

    /**
     * @return PinMatches
     */
    public function getParsedMatches(): PinMatches
    {
        return $this->parsedMatches;
    }

    /**
     * @return int
     */
    public function getPinLifetime(): int
    {
        return $this->pinLifetime;
    }
}