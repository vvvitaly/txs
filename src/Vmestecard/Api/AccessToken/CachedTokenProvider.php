<?php

declare(strict_types=1);

namespace App\Vmestecard\Api\AccessToken;

use Psr\SimpleCache\CacheInterface;

/**
 * Cache access token. This token provider tries to read access token from cache with the timestamp when token was
 * cached. If token is found and is still valid it returns without calling wrapped provider. Otherwise the inner
 * provider will be called.
 */
final class CachedTokenProvider implements TokenProviderInterface
{
    /**
     * @var TokenProviderInterface
     */
    private $inner;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @param TokenProviderInterface $inner
     * @param CacheInterface $cache
     * @param string $cachePrefix
     */
    public function __construct(TokenProviderInterface $inner, CacheInterface $cache, string $cachePrefix)
    {
        $this->inner = $inner;
        $this->cache = $cache;
        $this->cachePrefix = $cachePrefix;
    }


    /**
     * @inheritDoc
     */
    public function getToken(): ApiToken
    {
        $key = "{$this->cachePrefix}access-token";

        /** @noinspection PhpUnhandledExceptionInspection */
        $tokenData = $this->cache->get($key);

        $token = null;
        if ($tokenData !== null) {
            [$cachedTimestamp, $tokenValue, $lifetime] = $tokenData;
            $token = new ApiToken($tokenValue, $lifetime - (time() - $cachedTimestamp));
        }

        if (!$token || !$token->isValid()) {
            $token = $this->inner->getToken();
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->cache->set($key, [time(), $token->getToken(), $token->getLifetime()], $token->getLifetime());
        }

        return $token;
    }
}