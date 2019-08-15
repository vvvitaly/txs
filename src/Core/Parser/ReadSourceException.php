<?php

declare(strict_types=1);

namespace App\Core\Parser;

use RuntimeException;

/**
 * Can not read bills from source
 */
final class ReadSourceException extends RuntimeException
{
}