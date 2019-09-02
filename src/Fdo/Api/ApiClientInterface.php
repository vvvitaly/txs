<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api;

/**
 * Obtain bill by the FDO API request.
 */
interface ApiClientInterface
{
    /**
     * Get cheque instance by the corresponding request. Returns null if can not obtain bill with this API.
     *
     * @param FdoRequest $request
     *
     * @return FdoCheque|null
     * @throw ApiRequestException
     */
    public function getCheque(FdoRequest $request): ?FdoCheque;
}