<?php

declare(strict_types=1);

namespace vvvitaly\txs\Core\Bills;

/**
 * Bill type:
 * - expense
 * - income
 */
final class BillType
{
    private const EXPENSE = '-';
    private const INCOME = '+';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    private function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Create income type
     *
     * @return static
     */
    public static function income(): self
    {
        return new static(self::INCOME);
    }

    /**
     * Create expense type
     *
     * @return static
     */
    public static function expense(): self
    {
        return new static(self::EXPENSE);
    }

    /**
     * Check if the given bill is income
     *
     * @param \vvvitaly\txs\Core\Bills\BillType $billType
     *
     * @return bool
     */
    public static function isIncome(BillType $billType): bool
    {
        return $billType->type === self::INCOME;
    }

    /**
     * Check if the given bill is expense
     *
     * @param \vvvitaly\txs\Core\Bills\BillType $billType
     *
     * @return bool
     */
    public static function isExpense(BillType $billType): bool
    {
        return $billType->type === self::EXPENSE;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->type;
    }
}