<?php

declare(strict_types=1);

namespace tests\Vmestecard\Api\AccessToken;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use vvvitaly\txs\Vmestecard\Api\AccessToken\ApiToken;
use vvvitaly\txs\Vmestecard\Api\AccessToken\CachedTokenProvider;
use vvvitaly\txs\Vmestecard\Api\AccessToken\TokenProviderInterface;

/** @noinspection PhpMissingDocCommentInspection */

final class CachedTokenProviderTest extends TestCase
{
    public function testGetTokenWhenNoCache(): void
    {
        $token = new ApiToken('test', 1);

        $inner = $this->createMock(TokenProviderInterface::class);
        $inner->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('txs.access-token')
            ->willReturn(null);

        $tokenProvider = new CachedTokenProvider($inner, $cache, 'txs.');
        $actual = $tokenProvider->getToken();

        $this->assertSame($token, $actual);
    }

    public function testGetTokenFromCache(): void
    {
        $inner = $this->createMock(TokenProviderInterface::class);
        $inner->expects($this->never())
            ->method('getToken');

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('txs.access-token')
            ->willReturn([strtotime('-1 hour'), 'some token', 4000]);

        $tokenProvider = new CachedTokenProvider($inner, $cache, 'txs.');
        $actual = $tokenProvider->getToken();

        $this->assertEquals('some token', $actual->getToken());
        $this->assertEquals(400, $actual->getLifetime());
    }

    public function testGetTokenFromCacheButExpired(): void
    {
        $token = new ApiToken('test', 1);

        $inner = $this->createMock(TokenProviderInterface::class);
        $inner->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('txs.access-token')
            ->willReturn([strtotime('-1 hour'), 'some token', 3600]);

        $tokenProvider = new CachedTokenProvider($inner, $cache, 'txs.');
        $actual = $tokenProvider->getToken();

        $this->assertSame($token, $actual);
    }
}