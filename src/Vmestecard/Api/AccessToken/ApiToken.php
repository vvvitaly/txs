<?php

declare(strict_types=1);

namespace App\Vmestecard\Api\AccessToken;

/**
 * Token for using API. Contains token value and its lifetime in seconds.
 */
final class ApiToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $lifetime;

    /**
     * @param string $token
     * @param int $lifetime
     */
    public function __construct(string $token, int $lifetime)
    {
        $this->token = $token;
        $this->lifetime = $lifetime;
    }

    /**
     * Create token instance by '/token' API response.
     *
     * @param array $apiResponse
     *
     * @return ApiToken
     */
    public static function fromResponse(array $apiResponse): self
    {
        return new static(
            $apiResponse['access_token'],
            (int)$apiResponse['expires_in']
        );
    }

    /**
     * Check if token is valid for using.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->token && $this->lifetime > 0;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }
}