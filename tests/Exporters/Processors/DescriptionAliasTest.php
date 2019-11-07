<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Core\Export\Data\Transaction;
use vvvitaly\txs\Core\Export\Data\TransactionSplit;
use vvvitaly\txs\Exporters\Processors\DescriptionAlias;

final class DescriptionAliasTest extends TestCase
{
    /**
     * @param array $aliases
     * @param string|null $description
     * @param string|null $expectedDescription
     *
     * @dataProvider providerInvoke
     */
    public function testProcess(array $aliases, ?string $description, ?string $expectedDescription): void
    {
        $tx = new Transaction();
        $tx->description = $description;

        $proc = new DescriptionAlias($aliases);
        $proc->process($tx);

        $this->assertEquals($expectedDescription, $tx->description);
    }

    /**
     * @param array $aliases
     * @param string|null $description
     * @param string|null $expectedDescription
     *
     * @dataProvider providerInvoke
     */
    public function testProcessItem(array $aliases, ?string $description, ?string $expectedDescription): void
    {
        $tx = new Transaction();

        $item = new TransactionSplit();
        $item->memo = $description;
        $tx->splits = [$item];

        $proc = new DescriptionAlias($aliases);
        $proc->process($tx);

        $this->assertEquals($expectedDescription, $tx->splits[0]->memo);
    }

    public function providerInvoke(): array
    {
        // [array $aliases, ?string $description, ?string $expectedDescription]
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