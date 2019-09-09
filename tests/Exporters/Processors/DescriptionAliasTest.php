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
     * @param string|null $expectedAccount
     *
     * @dataProvider providerInvoke
     */
    public function testProcess(array $aliases, ?string $description, ?string $expectedAccount): void
    {
        $tx = new Transaction();
        $tx->description = $description;

        $proc = new DescriptionAlias($aliases);
        $proc->process($tx);

        $this->assertEquals($expectedAccount, $tx->account);
    }

    /**
     * @param array $aliases
     * @param string|null $description
     * @param string|null $expectedAccount
     *
     * @dataProvider providerInvoke
     */
    public function testProcessItem(array $aliases, ?string $description, ?string $expectedAccount): void
    {
        $tx = new Transaction();

        $item = new TransactionSplit();
        $item->memo = $description;
        $tx->splits = [$item];

        $proc = new DescriptionAlias($aliases);
        $proc->process($tx);

        $this->assertEquals($expectedAccount, $tx->splits[0]->account);
    }

    public function testProcessIfAccountAlreadySet(): void
    {
        $tx = new Transaction();
        $tx->account = 'XXX';
        $tx->description = 'some';

        $item = new TransactionSplit();
        $item->memo = 'some';
        $item->account = 'ZZZ';
        $tx->splits = [$item];

        $proc = new DescriptionAlias(['YYY' => ['some']]);
        $proc->process($tx);

        $this->assertEquals('XXX', $tx->account);
        $this->assertEquals('some', $tx->description);
        $this->assertEquals('ZZZ', $tx->splits[0]->account);
        $this->assertEquals('some', $tx->splits[0]->memo);
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