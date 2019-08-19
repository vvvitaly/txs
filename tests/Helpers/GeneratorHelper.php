<?php

declare(strict_types=1);

namespace tests\Helpers;

use Generator;

/**
 * Test PHP generators
 */
final class GeneratorHelper
{
    /**
     * Creates generator from array. It's useful with mock willReturn method.
     *
     * @param array $data
     *
     * @return Generator
     */
    public static function fromArray(array $data): Generator
    {
        foreach ($data as $item) {
            yield $item;
        }
    }
}