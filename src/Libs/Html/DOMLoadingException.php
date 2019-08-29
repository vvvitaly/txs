<?php

declare(strict_types=1);

namespace App\Libs\Html;

use RuntimeException;

/**
 * Can not load DOM document
 */
final class DOMLoadingException extends RuntimeException
{
}