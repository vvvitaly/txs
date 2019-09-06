<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Exporters\Processors\CurrencyNormalizer;

final class CurrencyNormalizerTest extends TestCase
{
    /**
     * @param string $originCurrency
     * @param string $expectedCurrency
     *
     * @dataProvider providerProcess
     */
    public function testProcess(?string $originCurrency, ?string $expectedCurrency): void
    {
        $tx = new Transaction();
        $tx->currency = $originCurrency;

        $proc = new CurrencyNormalizer();
        $proc->process($tx);

        $this->assertEquals($expectedCurrency, $tx->currency);
    }

    public function providerProcess(): array
    {
        return [
            '$' => ['$', 'USD'],
            'USD' => ['USD', 'USD'],
            'руб' => ['руб', 'RUB'],
            'Руб' => ['руб', 'RUB'],
            'руб.' => ['руб.', 'RUB'],
            'р' => ['р', 'RUB'],
            'р.' => ['р.', 'RUB'],
            '₽' => ['р.', 'RUB'],
            'unknown' => ['xxx', 'xxx'],
            'undefined' => [null, null],
        ];
    }

    public function testProcessWithCustomCurrency(): void
    {
        $tx = new Transaction();
        $tx->currency = 'xxx';

        $proc = new CurrencyNormalizer();

        $proc->addACurrencyAlias('RUB', ['xxx']);
        $proc->process($tx);
        $this->assertEquals('RUB', $tx->currency);
    }
}