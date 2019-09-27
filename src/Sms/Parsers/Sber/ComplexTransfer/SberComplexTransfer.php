<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\Composer;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage;
use vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberValidationTrait;

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
final class SberComplexTransfer implements MessageParserInterface
{
    use SberValidationTrait;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface
     */
    private $storage;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface
     */
    private $pinSmsParser;

    /**
     * @var \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface
     */
    private $transferSmsParser;

    /**
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\MessagesStorageInterface $storage
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinSmsParserInterface $pinSmsParser
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationSmsParserInterface $transferSmsParser
     */
    public function __construct(
        MessagesStorageInterface $storage,
        PinSmsParserInterface $pinSmsParser,
        ConfirmationSmsParserInterface $transferSmsParser
    ) {
        $this->storage = $storage;
        $this->pinSmsParser = $pinSmsParser;
        $this->transferSmsParser = $transferSmsParser;
    }

    /**
     * @inheritDoc
     */
    public function parse(Message $sms): ?Bill
    {
        if (!$this->isValid($sms)) {
            return null;
        }

        $pin = $this->pinSmsParser->parseSms($sms);
        if ($pin !== null) {
            $this->storage->savePinMessage($pin);

            return null;
        }

        $transfer = $this->transferSmsParser->parseSms($sms);
        if ($transfer !== null) {
            $pin = $this->storage->findPinMessage($transfer);
        }

        if ($pin !== null && $transfer !== null) {
            return $this->createBill($pin, $transfer);
        }

        return null;
    }

    /**
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\PinMessage $pinMessage
     *
     * @param \vvvitaly\txs\Sms\Parsers\Sber\PinParser\ConfirmationMessage $transferMessage
     *
     * @return \vvvitaly\txs\Core\Bills\Bill
     */
    private function createBill(PinMessage $pinMessage, ConfirmationMessage $transferMessage): Bill
    {
        return Composer::expenseBill()
            ->setAmount($pinMessage->amount)
            ->setCurrency($pinMessage->currency)
            ->setAccount($transferMessage->account)
            ->setDescription($pinMessage->description)
            ->setDate($transferMessage->transferDate)
            ->getBill();
    }
}