<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Bills composer
 */
final class Composer
{
    /**
     * @var \vvvitaly\txs\Core\Bills\Bill
     */
    private $bill;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string|null
     */
    private $currency;

    /**
     * @var string
     */
    private $account;

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $billNumber;

    /**
     * @var \vvvitaly\txs\Core\Bills\BillItem[]
     */
    private $items = [];

    /**
     * @return \vvvitaly\txs\Core\Bills\Composer
     */
    public static function newBill(): Composer
    {
        return new static();
    }

    /**
     * @param float $amount
     *
     * @return Composer
     */
    public function setAmount(float $amount): Composer
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param string|null $currency
     *
     * @return Composer
     */
    public function setCurrency(?string $currency): Composer
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @param string $account
     *
     * @return Composer
     */
    public function setAccount(string $account): Composer
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @param \DateTimeImmutable $date
     *
     * @return Composer
     */
    public function setDate(DateTimeImmutable $date): Composer
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param string|null $description
     *
     * @return Composer
     */
    public function setDescription(?string $description): Composer
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param \vvvitaly\txs\Core\Bills\BillInfo $info
     *
     * @return \vvvitaly\txs\Core\Bills\Composer
     */
    public function setBillInfo(BillInfo $info): Composer
    {
        $this->date = $info->getDate();
        $this->description = $info->getDescription();
        $this->billNumber = $info->getNumber();

        return $this;
    }

    /**
     * @param string|null $billNumber
     *
     * @return Composer
     */
    public function setBillNumber(?string $billNumber): Composer
    {
        $this->billNumber = $billNumber;

        return $this;
    }

    /**
     * @param \vvvitaly\txs\Core\Bills\BillItem[] $items
     *
     * @return Composer
     */
    public function setItems(BillItem ...$items): Composer
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add a bill item.
     *
     * @param float $amount
     * @param string|null $description
     * @param string|null $currency
     *
     * @return \vvvitaly\txs\Core\Bills\Composer
     */
    public function addItem(float $amount, ?string $description, ?string $currency = null): Composer
    {
        $this->items[] = new BillItem(
            $description,
            new Amount($amount, $currency)
        );

        return $this;
    }

    /**
     * @return \vvvitaly\txs\Core\Bills\Bill
     * @throws \InvalidArgumentException
     */
    public function getBill(): Bill
    {
        if ($this->bill) {
            return $this->bill;
        }

        $this->validate();

        $this->bill = new Bill(
            new Amount($this->amount, $this->currency),
            $this->account,
            new BillInfo($this->date, $this->description, $this->billNumber),
            $this->items
        );

        return $this->bill;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if (!$this->amount) {
            throw new InvalidArgumentException('Amount is required');
        }

        if (!$this->account) {
            throw new InvalidArgumentException('Account is required');
        }
    }
}