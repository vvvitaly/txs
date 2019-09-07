<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors\Normalizers;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Exporters\Processors\Normalizers\WordsNormalizer;

final class WordsNormalizerTest extends TestCase
{
    /**
     * @param string|null $text
     * @param string|null $expected
     *
     * @dataProvider providerInvoke
     */
    public function testInvoke(?string $text, ?string $expected): void
    {
        $this->assertEquals($expected, (new WordsNormalizer())($text));
    }

    public function providerInvoke(): array
    {
        return [
            'null' => [null, null],
            'no letters' => ['4*3499492 Вафли 150г', ' Вафли 150г'],
            'no letters 2' => ['1:36452820 Вафли 150г', ' Вафли 150г'],
            'no letters 3' => ['4921 Pull&Bear', ' Pull&Bear'],
            'no letters 4' => ['Мешки д/мусора *24', 'Мешки д/мусора '],
            'with letters' => ['4921Pull&Bear', '4921Pull&Bear'],
        ];
    }
}