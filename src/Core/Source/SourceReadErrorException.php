<?php

declare(strict_types=1);

namespace App\Core\Source;

use RuntimeException;

/**
 * Occurs if source can't be read.
 */
final class SourceReadErrorException extends RuntimeException
{
}