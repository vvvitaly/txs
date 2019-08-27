<?php

declare(strict_types=1);

namespace App\Ofd\Api;

use App\Core\Bills\Amount;
use App\Core\Bills\Bill;
use App\Core\Bills\BillInfo;
use App\Core\Bills\BillItem;
use App\Core\Bills\BillsCollection;
use App\Core\Source\BillSourceInterface;
use App\Core\Source\SourceReadException;
use Webmozart\Assert\Assert;

/**
 * Get the bills by the list of QR codes content. This class requests one of the OFD API.
 * If the given API client can not perform request, the source reading exception is thrown.
 *
 * @see ApiClientInterface
 * @see OfdRequest
 * @see SourceReadException
 */
final class OfdApiSource implements BillSourceInterface
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
     * @param OfdRequest[] $requestsList
     * @param ApiClientInterface $apiClient
     * @param string $defaultAccount
     */
    public function __construct(array $requestsList, ApiClientInterface $apiClient, string $defaultAccount)
    {
        Assert::allIsInstanceOf($requestsList, OfdRequest::class);

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
            } catch (ApiRequestException $exception) {
                throw new SourceReadException('Can not perform API request', 0, $exception);
            }

            if ($cheque) {
                $bills[] = $this->parseCheque($cheque);
            }
        }

        return new BillsCollection(...$bills);
    }

    /**
     * Create bill instance based on OFD response.
     *
     * @param OfdCheque $cheque
     *
     * @return Bill
     */
    private function parseCheque(OfdCheque $cheque): Bill
    {
        $items = [];
        foreach ($cheque->items as $item) {
            $items[] = new BillItem($item[0], new Amount($item[1]));
        }

        return new Bill(
            new Amount($cheque->totalAmount),
            $this->defaultAccount,
            new BillInfo($cheque->date, $cheque->place, $cheque->number),
            $items
        );
    }
}