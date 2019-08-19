<?php

declare(strict_types=1);

namespace App\Vmestecard\Api;

use DomainException;

/**
 * Failed API call (error response, transfer error, etc.)
 */
final class ApiErrorException extends DomainException
{
}