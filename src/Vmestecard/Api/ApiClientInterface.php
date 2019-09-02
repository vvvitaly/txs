<?php

declare(strict_types=1);

namespace vvvitaly\txs\Vmestecard\Api;

use vvvitaly\txs\Libs\Date\DatesRange;
use vvvitaly\txs\Vmestecard\Api\Client\Pagination;

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