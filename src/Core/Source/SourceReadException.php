<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Source;

use RuntimeException;

/**
 * Occurs if source can't be read.
 */
final class SourceReadException extends RuntimeException
{
}