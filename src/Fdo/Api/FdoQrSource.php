<?php

declare(strict_types=1);

namespace vvvitaly\txs\Fdo\Api;

use vvvitaly\txs\Core\Bills\Amount;
use vvvitaly\txs\Core\Bills\Bill;
use vvvitaly\txs\Core\Bills\BillInfo;
use vvvitaly\txs\Core\Bills\BillItem;
use vvvitaly\txs\Core\Bills\BillsCollection;
use vvvitaly\txs\Core\Source\BillSourceInterface;
use vvvitaly\txs\Core\Source\SourceReadException;
use Webmozart\Assert\Assert;

/**
 * Get the bills by the list of QR codes content. This class requests one of the Fiscal Data Operator (FDO) API.
 * If the given API client can not perform request, the source reading exception is thrown.
 *
 * @see ApiClientInterface
 * @see FdoRequest
 * @see SourceReadException
 */
final class FdoQrSource implements BillSourceInterface
{
    /**
     * @var string[]
     */
    private $requestsList;

    /**
     * @var ApiClientInterface
     */
    private $apiClient;

    /**
     * @var string
     */
    private $defaultAccount;

    /**
     * @param FdoRequest[] $requestsList
     * @param ApiClientInterface $apiClient
     * @param string $defaultAccount
     */
    public function __construct(array $requestsList, ApiClientInterface $apiClient, string $defaultAccount)
    {
        Assert::allIsInstanceOf($requestsList, FdoRequest::class);

        $this->requestsList = $requestsList;
        $this->apiClient = $apiClient;
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * @inheritDoc
     */
    public function read(): BillsCollection
    {
        $bills = [];
        foreach ($this->requestsList as $request) {
            try {
                $cheque = $this->apiClient->getCheque($request);
            } catch (ApiErrorException $exception) {
                throw new SourceReadException('Can not perform API request', 0, $exception);
            }

            if ($cheque) {
                $bills[] = $this->parseCheque($cheque);
            }
        }

        return new BillsCollection(...$bills);
    }

    /**
     * Create bill instance based on FDO response.
     *
     * @param FdoCheque $cheque
     *
     * @return Bill
     */
    private function parseCheque(FdoCheque $cheque): Bill
    {
        $items = [];
        foreach ($cheque->items as $item) {
            $items[] = new BillItem($item->name, new Amount($item->amount));
        }

        return new Bill(
            new Amount($cheque->totalAmount),
            $this->defaultAccount,
            new BillInfo($cheque->date, $cheque->place, $cheque->number),
            $items
        );
    }
}