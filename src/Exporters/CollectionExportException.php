<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters;

use DomainException;
use Throwable;
use vvvitaly\txs\Core\Bills\Bill;

/**
 * Occurs if can not parse some bill from collection
 */
final class CollectionExportException extends DomainException
{
    /**
     * @var Bill
     */
    private $bill;

    /**
     * @param Bill $bill
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Bill $bill, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->bill = $bill;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Bill
     */
    public function getBill(): Bill
    {
        return $this->bill;
    }
}