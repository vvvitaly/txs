<?php

declare(strict_types=1);

namespace vvvitaly\txs\Exporters\Processors\Normalizers;

/**
 * Remove units from description
 */
final class UnitsNormalizer
{
    /**
     * @var string[]
     */
    private $units = [];

    private static $defaultUnits = [
        'гр?',
        'кг',
        'мг',
        'л',
        'мл',
        'L',
        'шт',
        'см',
        'cm',
        '%',
        'мкр',
    ];

    /**
     * @param string[] $units
     */
    public function __construct(?array $units = null)
    {
        $this->units = $units ?: self::$defaultUnits;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(?string $text): ?string
    {
        if (!$text) {
            return $text;
        }

        $units = implode('|', $this->units);

        return preg_replace('/[\d.,]+\s?(?:' . $units . ')\.?(?:\*\d+)?/iu', '', $text);
    }
}