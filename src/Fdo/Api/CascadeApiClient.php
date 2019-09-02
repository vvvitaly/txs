<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api;

/**
 * Try to find cheque with several APIs.
 */
final class CascadeApiClient implements ApiClientInterface
{
    /**
     * @var ApiClientInterface
     */
    private $apiList;

    /**
     * @param ApiClientInterface ...$apiList
     */
    public function __construct(ApiClientInterface ...$apiList)
    {
        $this->apiList = $apiList;
    }

    /**
     * @inheritDoc
     */
    public function getCheque(FdoRequest $request): ?FdoCheque
    {
        foreach ($this->apiList as $apiClient) {
            $cheque = $apiClient->getCheque($request);
            if ($cheque) {
                return $cheque;
            }
        }

        return null;
    }
}