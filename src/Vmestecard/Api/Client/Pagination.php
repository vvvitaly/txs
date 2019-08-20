<?php

declare(strict_types=1);

namespace App\Vmestecard\Api\Client;

/**
 * API pagination
 */
final class Pagination
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Calculate first page offset
     */
    public function reset(): void
    {
        $this->offset = 0;
    }

    /**
     * Calculate next page offset
     */
    public function nextPage(): void
    {
        $this->offset += $this->limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}