<?php

declare(strict_types=1);

namespace App\Vmestecard\Api\AccessToken;

/**
 * Provides token for API
 */
interface TokenProviderInterface
{
    /**
     * @return ApiToken
     * @throws TokenNotFoundException
     */
    public function getToken(): ApiToken;
}