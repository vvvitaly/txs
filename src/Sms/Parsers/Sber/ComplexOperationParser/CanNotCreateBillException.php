<?php

declare(strict_types=1);

namespace vvvitaly\txs\Sms\Parsers\Sber\ComplexOperationParser;

use RuntimeException;

/**
 * Raised with BillsFactoryInterface can not create a bill instance.
 */
final class CanNotCreateBillException extends RuntimeException
{
}