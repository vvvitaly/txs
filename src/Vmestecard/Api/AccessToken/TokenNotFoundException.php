<?php

declare(strict_types=1);

namespace App\Vmestecard\Api\AccessToken;

use RuntimeException;

/**
 * Can not obtain access token
 */
final class TokenNotFoundException extends RuntimeException
{

}