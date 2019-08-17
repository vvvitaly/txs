<?php

declare(strict_types=1);

namespace App\Sms;

use DomainException;

/**
 * Occurs when parser can not process given SMS
 */
final class UnknownSmsTypeException extends DomainException
{
}