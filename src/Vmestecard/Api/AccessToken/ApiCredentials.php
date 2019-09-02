<?php

declare(strict_types=1);

namespace vvvitaly\txs\Vmestecard\Api\AccessToken;

/**
 * Credentials for API
 */
final class ApiCredentials
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}