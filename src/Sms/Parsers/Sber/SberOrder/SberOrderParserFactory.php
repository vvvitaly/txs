<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\SberOrder;

use vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser\SberComplexOperationParser;

/**
 * Parse messages about orders. This operation contains two steps: PIN message and confirmation messages. A PIN message
 * contains information about store, account name, amount value and order ID. A confirmation message contains account,
 * amount and order ID. Both messages related to each other by the same store name and order ID.
 * PIN message format:
 *  - "Проверьте реквизиты платежа: интернет-магазин {store name}, заказ: {order ID}, сумма: {amount} {currency}.
 *     Для оплаты с карты {account} отправьте пароль {pin code} на номер 900."
 * Confirmation message format:
 *  "Заказ {order ID} в магазине {store name} на сумму {amount} {currency} успешно оплачен."
 */
final class SberOrderParserFactory
{
    /**
     * @return SberComplexOperationParser
     */
    public function getParser(): SberComplexOperationParser
    {
        return new SberComplexOperationParser(
            new OrderStepParser(),
            new BillsFactory()
        );
    }
}