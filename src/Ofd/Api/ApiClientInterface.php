<?php

declare(strict_types=1);

namespace App\Ofd\Api;

/**
 * Obtain bill by the OFD request
 */
interface ApiClientInterface
{
    /**
     * Get cheque instance by the corresponding request. Returns null if can not obtain bill with this API.
     *
     * @param OfdRequest $request
     *
     * @return OfdCheque|null
     * @throw ApiRequestException
     */
    public function getCheque(OfdRequest $request): ?OfdCheque;
}