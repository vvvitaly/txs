<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Export;

use DomainException;

/**
 * Occurs if specified bill doesn't valid for export
 */
final class InvalidBillException extends DomainException
{
}