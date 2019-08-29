<?php

declare(strict_types=1);

namespace App\Fdo\Api\Clients;

use RuntimeException;

/**
 * Occurs when parser can not parse response.
 */
final class ResponseParseException extends RuntimeException
{
}