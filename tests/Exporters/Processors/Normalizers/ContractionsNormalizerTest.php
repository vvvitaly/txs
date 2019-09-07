<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors\Normalizers;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Exporters\Processors\Normalizers\ContractionsNormalizer;

final class ContractionsNormalizerTest extends TestCase
{
    /**
     * @param string|null $text
     * @param string|null $expected
     *
     * @dataProvider providerInvoke
     */
    public function testInvoke(?string $text, ?string $expected): void
    {
        $this->assertEquals($expected, (new ContractionsNormalizer())($text));
    }

    public function providerInvoke(): array
    {
        return [
            'null' => [null, null],
            'first abbr' => ['Д/п сок', ' сок'],
            'rand abbr 1' => ['Что-то x/x', 'Что-то '],
            'rand abbr 2' => ['Что-то ц/й', 'Что-то '],
            'multiple abbr' => ['Что-то ц/й у/л', 'Что-то  '],
            'spec abbr 1' => ['Колбаса п/сух вес', 'Колбаса  вес'],
            'unknown abbr 1' => ['Колбаса п/абв вес', 'Колбаса п/абв вес'],
            'дет.' => ['Мыло жидкое дет.', 'Мыло жидкое '],
            'дет' => ['Мыло жидкое дет', 'Мыло жидкое дет'],
            'not abbr' => ['Сок Ябл/персик/абрик', 'Сок Ябл/персик/абрик'],
        ];
    }
}