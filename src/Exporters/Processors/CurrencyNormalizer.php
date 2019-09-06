<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors;

use vvvitaly\txs\Core\Export\Data\Transaction;

/**
 * Normalize currency code (to ISO 4217):
 *  - uppercase
 *  - replace abbreviations with codes
 */
final class CurrencyNormalizer implements ProcessorInterface
{
    /**
     * @var array
     */
    private $map;

    public function __construct()
    {
        $this->map = self::$defaultMap;
    }

    /**
     * Add possible aliases for the given currency (ISO 4217). Every alias in the list will be replaces with this code.
     *
     * @param string $currencyIsoCode
     * @param array $aliases
     */
    public function addACurrencyAlias(string $currencyIsoCode, array $aliases): void
    {
        foreach ($aliases as $alias) {
            $this->map[strtolower($alias)] = $currencyIsoCode;
        }
    }

    /**
     * @inheritDoc
     */
    public function process(Transaction $transaction): void
    {
        if ($transaction->currency === null) {
            return;
        }

        $originCurrency = strtolower($transaction->currency);
        if (isset($this->map[$originCurrency])) {
            $transaction->currency = $this->map[$originCurrency];
        }
    }

    /**
     * Default replace map
     * @var array
     */
    private static $defaultMap = [
        'р' => 'RUB',
        'р.' => 'RUB',
        'руб' => 'RUB',
        'руб.' => 'RUB',
        '₽' => 'RUB',
        '$' => 'USD',
        'долл.' => 'USD',
        '€' => 'EUR',
    ];
}