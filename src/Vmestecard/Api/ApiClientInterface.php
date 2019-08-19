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
     * Load transactions history for the specified date range.
     *
     * @param DateRange $dateRange
     *
     * @return array
     * @throws ApiErrorException
     */
    public function getHistory(DateRange $dateRange): array;
}