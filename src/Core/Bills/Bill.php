<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

/**
 * Model of some parsed bill. Bill is a document which proves a purchase. This model represent such document and can be
 * obtained from different sources (text files, bank reports, recognized photos of real bills, some APIs, etc.)
 *
 * Bill has the following attributes:
 * - total spent amount (optional, with currency)
 * - debited account name
 * - date of purchase
 * - some purchase description
 * - bill number
 * - list of purchased goods (optional, name + price)
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