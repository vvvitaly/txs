<?php

declare(strict_types=1);

namespace vvvitaly\txs\Vmestecard\Api\AccessToken;

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