<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer;

use vvvitaly\txs\Sms\Message;

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
interface TransferSmsParserInterface
{
    /**
     * Parse the message. If SMS is NOT transfer SMS returns null.
     *
     * @param \vvvitaly\txs\Sms\Message $message
     *
     * @return \vvvitaly\txs\Sms\Parsers\Sber\ComplexTransfer\TransferMessage|null
     */
    public function parseSms(Message $message): ?TransferMessage;
}