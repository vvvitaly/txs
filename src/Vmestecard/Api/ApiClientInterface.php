<?php

declare(strict_types=1);

namespace App\Vmestecard\Api;

use App\Libs\Date\DatesRange;
use App\Vmestecard\Api\Client\Pagination;

/**
 * Vmestecard API client
 */
interface ApiClientInterface
{
    /**
     * Load transactions history for the specified date range and page settings.
     *
     * @param DatesRange $dateRange
     * @param Pagination $pagination
     *
     * @return array
     */
    public function getHistory(DatesRange $dateRange, Pagination $pagination): array;
}