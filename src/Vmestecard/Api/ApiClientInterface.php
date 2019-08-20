<?php

declare(strict_types=1);

namespace App\Vmestecard\Api;

use App\Libs\Date\DateRange;

/**
 * Vmestecard API client
 */
interface ApiClientInterface
{
    /**
     * Load transactions history for the specified date range and page settings.
     *
     * @param DateRange $dateRange
     * @param Pagination $pagination
     *
     * @return array
     */
    public function getHistory(DateRange $dateRange, Pagination $pagination): array;
}