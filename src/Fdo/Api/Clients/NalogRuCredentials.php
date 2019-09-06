<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api\Clients;

/**
 * Credentials for authorization in NalogRu client
 */
final class NalogRuCredentials
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

    /**
     * Generate authorization header
     *
     * @return string
     */
    public function getAuthHeaderLine(): string
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }
}