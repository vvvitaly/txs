<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors\Normalizers;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Exporters\Processors\Normalizers\UnitsNormalizer;

final class UnitsNormalizerTest extends TestCase
{
    /**
     * @param string|null $text
     * @param string|null $expected
     *
     * @dataProvider providerInvoke
     */
    public function testInvoke(?string $text, ?string $expected): void
    {
        $this->assertEquals($expected, (new UnitsNormalizer())($text));
    }

    public function providerInvoke(): array
    {
        return [
            'null' => [null, null],
            'г' => ['Ветчина 400г', 'Ветчина '],
            'г & space' => ['Ветчина 400 г', 'Ветчина '],
            'гр' => ['Ветчина 400гр', 'Ветчина '],
            'гр.' => ['Ветчина 400 гр.', 'Ветчина '],
            'штуки' => ['Что-то 20 30шт', 'Что-то 20 '],
            'штуки.' => ['Что-то 20 30шт.', 'Что-то 20 '],
            'штуки & space' => ['Что-то 20 30 шт', 'Что-то 20 '],
            'units & quantity' => ['Что-то 30шт*20', 'Что-то '],
            'units, dot & quantity' => ['Что-то 30кг.*20', 'Что-то '],
            'units & (not)quantity' => ['Что-то 30шт* 20', 'Что-то * 20'],
            'L' => ['Сок 2L', 'Сок '],
            'л' => ['Сок 2л', 'Сок '],
            'см' => ['Круг 25 см', 'Круг '],
            'many spaces' => ['Круг 25    см', 'Круг 25    см'],
            'decimals' => ['Сок 0.2л.', 'Сок '],
            'decimals/comma' => ['Сок 0,2л', 'Сок '],
            'multiple units' => ['Что-то 5г 6 шт.', 'Что-то  '],
            'real 1' => ['Чипсы ЛЕЙЗ STAX 110гр.*9 сметана и лук', 'Чипсы ЛЕЙЗ STAX  сметана и лук'],
        ];
    }
}