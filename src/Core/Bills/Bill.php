<?php

declare(strict_types=1);

namespace App\Core\Bills;

/**
 * Parsed bill
 */
final class Bill
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * Billed account
     *
     * @var string|null
     */
    private $account;

    /**
     * @var BillInfo
     */
    private $info;

    /**
     * Bill items
     *
     * @var BillItem[]
     */
    private $items;

    /**
     * @param Amount $amount
     * @param string|null $account
     * @param BillInfo|null $info
     * @param BillItem[] $items
     */
    public function __construct(
        Amount $amount,
        ?string $account = null,
        ?BillInfo $info = null,
        ?array $items = null
    ) {
        $this->amount = $amount;
        $this->account = $account;
        $this->info = $info ?? new BillInfo();
        $this->items = $items ?? [];
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function getAccount(): ?string
    {
        return $this->account;
    }

    /**
     * @return BillInfo
     */
    public function getInfo(): BillInfo
    {
        return $this->info;
    }

    /**
     * @return BillItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}