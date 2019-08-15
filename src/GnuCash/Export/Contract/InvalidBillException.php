<?php

declare(strict_types=1);

namespace App\GnuCash\Export\Contract;

use DomainException;

/**
 * Occurs if specified bill doesn't valid for export
 */
final class InvalidBillException extends DomainException
{
}