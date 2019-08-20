<?php

declare(strict_types=1);

namespace App\Vmestecard\Api;

/**
 * Provides token for API
 */
interface TokenProviderInterface
{
    /**
     * @return ApiToken
     */
    public function getToken(): ApiToken;
}