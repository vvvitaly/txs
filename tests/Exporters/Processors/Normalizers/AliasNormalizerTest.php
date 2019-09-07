<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors\Normalizers;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Exporters\Processors\Normalizers\AliasNormalizer;

final class AliasNormalizerTest extends TestCase
{
    /**
     * @param array $aliases
     * @param string|null $text
     * @param string|null $expected
     *
     * @dataProvider providerInvoke
     */
    public function testInvoke(array $aliases, ?string $text, ?string $expected): void
    {
        $this->assertEquals($expected, (new AliasNormalizer($aliases))($text));
    }

    public function providerInvoke(): array
    {
        return [
            'null' => [[], null, null],
            'without aliases' => [[], 'some text', 'some text'],
            'basic' => [['tomatoes' => ['pomidorro', 'tomato']], 'Text ToMaTo XXX', 'tomatoes'],
            'only words' => [['tomatoes' => ['pomidorro', 'tomato']], 'tomatoword', 'tomatoword'],
            'already exists' => [['tomatoes' => ['pomidorro', 'tomato']], 'tomatoes', 'tomatoes'],
            'not found' => [['tomatoes' => ['pomidorro', 'tomato']], 'XXX YYY', 'XXX YYY'],
            'simple syntax' => [['tomatoes'], 'Some tomAToes', 'tomatoes'],
            'multiple matches, should take first' => [
                [
                    'tomatoes',
                    'apples',
                ],
                'Some apples & tomatoes',
                'tomatoes',
            ],
            'cyrillic' => [['томаты' => ['помидоры', 'томаты']], 'лучшие Помидоры', 'томаты'],
            'cyrillic, only words' => [['томаты' => ['подмидоры', 'томаты']], 'томатыслово', 'томатыслово'],
        ];
    }
}