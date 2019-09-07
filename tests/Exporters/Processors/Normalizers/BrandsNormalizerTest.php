<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors\Normalizers;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Exporters\Processors\Normalizers\BrandsNormalizer;

final class BrandsNormalizerTest extends TestCase
{
    /**
     * @param string|null $text
     * @param string|null $expected
     *
     * @dataProvider providerInvoke
     */
    public function testInvoke(?string $text, ?string $expected): void
    {
        $this->assertEquals($expected, (new BrandsNormalizer())($text));
    }

    public function providerInvoke(): array
    {
        return [
            'null' => [null, null],
            'brand in double quotes' => ['Мыло "что-то"', 'Мыло '],
            'brand in doubled double quotes' => ['Мыло ""что-то""', 'Мыло '],
            'brand in quotes' => ["Мыло 'что-то'", 'Мыло '],
            'brand in doubled quotes' => ["Мыло ''что-то''", 'Мыло '],
            'brand in brackets' => ['Молоко (завод)', 'Молоко '],
            'only one match' => ['Мыло "что-то" и "еще"', 'Мыло  и "еще"'],
        ];
    }
}